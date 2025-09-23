import math
import re

# ---------- Paramètres ----------
EPS = 35.0   # distance mini entre points consécutifs (px) -> supprime les points trop proches
TOL = 2.5   # tolérance Douglas–Peucker (0 = désactivé). Ex: 2.5 pour simplifier davantage
CLOSE_TOL = 2.5  # si dernier point ~ premier point < CLOSE_TOL -> fermer avec Z

# ---------- Ton path ----------
D_PATH = r"""
m160 975 3-7 3-11 8-23v-2l-1-14 1-15 2-45 1-27-6-8-13-19-1-3-2-12-3-10-2-9-2-5-6-14-8-11-9-13-9-13-2-2-8-13-8-12-3 1 1 3-2 1-27 3-23-45-6-46-3-21v-1l-4-35-2-15-3-24h-1l-1-16v-11l7 1h5l14 4 7 1 5 1h22l7-1 9-3 5-2 5-2 6-6 9-10 3-4 6-8 2-6 2-15 1-13-1-28-1-4 1-7 1-5 3-8 6-11 5-8 5-9 5-6 8-9 10-11 3-3 2-3 5-10 1-3 2-6 1-8v-10l5 1 8 3 4-4 6-3 6-7 4-5 5-9 1-3 3-2 3-1h11l8-2 5-2 26-13 30-11 19-8 20-8 12-4 32-12 48-19 28-11 14-6 15-6 9-4 15-4 7-3 3-7 15 8 24 5 22 6 41 16 6 2 7 1 26 3 27 4 10 2 11 2 18 5 8 3 6 3 8 3 15 8 7 5 4 4 10 11 5 7 7 9 6 7 11 11 6 7 5 4 7 5 12 8 16 10 17 9 7 3 6 3 5 1 14 3v2l22 3 20 4h3l-1 7-1 2-2 2-1 3 1 1 3-1v5l-3 4-1 4-1 1-4-4-3 1v3l-2 2 2 2h3l-4 5-2 4-5 2v2l1 2v3l1 3v10l-2 4-4 3-6 2 1 1 1 19-3-1-1 6v3l-1 14-5 1v9l2 7-5 2h-6l-6-1-7-1-2 1-3 8-3-1-1 3-2-1-2 6 2 7h-2l1 4-11 2 2 4-3 3 1 3-8 3v5l-2 3-2 1-6 1-8-1-5-1-1 4 1 7-1 7 1 3-7 7-5 5 1 2-2 1 4 5-7 5-4 2-1 4-4 9-3 6-6 5-2 4-7 8-6 4-8 5-8 1h-11l-4 1-9 6-4-1v12l3 18 4 26 1 14v5l-1 7-3 6-4 9-6 10-5 20-10 29-2 7-4 6-4 14v1l-14 37h-1l-9 22-5 14h1l-6 7-9 11 1 2 10 21 4 1 15 4-5 19-11 46-3 13H555l-22 1-92-1v1H322l-15 1-36 2-27 1-83 3-1-1z
""".strip()

# ---------- Parsing très simple pour M/m L/l H/V (si présents) ----------
token_re = re.compile(r'([MmLlHhVvZz])|([-+]?\d*\.?\d+)')

def path_to_points(d):
    tokens = token_re.findall(d)
    # flattener: [('M',''),('','10'),('','20'),('L',''),('','30')...]
    i = 0
    cmd = None
    x = y = 0.0
    startx = starty = None
    points = []
    def add_point(px, py):
        points.append((px, py))
    while i < len(tokens):
        tcmd, tnum = tokens[i]
        if tcmd:
            cmd = tcmd
            i += 1
            if cmd in 'Zz':
                # close path -> répète le premier point à la fin
                if startx is not None:
                    add_point(startx, starty)
                continue
        else:
            # number encountered without a new cmd: continue previous cmd
            pass

        if cmd in 'Mm':
            # moveto: pairs
            x0 = float(tokens[i][1]); y0 = float(tokens[i+1][1]); i += 2
            if cmd == 'm':
                x += x0; y += y0
            else:
                x, y = x0, y0
            add_point(x, y)
            startx, starty = x, y
            # subsequent coordinate pairs are implicit lineto
            while i < len(tokens) and tokens[i][0] == '' and i+1 < len(tokens):
                x0 = float(tokens[i][1]); y0 = float(tokens[i+1][1]); i += 2
                if cmd == 'm':
                    x += x0; y += y0
                else:
                    x, y = x0, y0
                add_point(x, y)

        elif cmd in 'Ll':
            # lineto: pairs
            while i < len(tokens) and tokens[i][0] == '' and i+1 < len(tokens):
                x0 = float(tokens[i][1]); y0 = float(tokens[i+1][1]); i += 2
                if cmd == 'l':
                    x += x0; y += y0
                else:
                    x, y = x0, y0
                add_point(x, y)

        elif cmd in 'Hh':
            # horizontal
            while i < len(tokens) and tokens[i][0] == '':
                x0 = float(tokens[i][1]); i += 1
                x = x + x0 if cmd == 'h' else x0
                add_point(x, y)

        elif cmd in 'Vv':
            # vertical
            while i < len(tokens) and tokens[i][0] == '':
                y0 = float(tokens[i][1]); i += 1
                y = y + y0 if cmd == 'v' else y0
                add_point(x, y)

        else:
            # autres commandes (C/Q/A...) non gérées ici
            raise ValueError(f"Commande non gérée: {cmd}")
    return points

def dist(a, b):
    return math.hypot(a[0]-b[0], a[1]-b[1])

def drop_close_points(pts, eps):
    if not pts: return pts
    out = [pts[0]]
    for p in pts[1:]:
        if dist(p, out[-1]) >= eps:
            out.append(p)
    # si la forme est fermée, vérifie le dernier vs premier
    if len(out) > 2 and dist(out[0], out[-1]) < eps:
        out[-1] = out[0]
    return out

# Douglas–Peucker
def rdp(points, epsilon):
    if len(points) < 3 or epsilon <= 0:
        return points[:]
    # distance point-segment
    def point_line_distance(p, a, b):
        # segment AB
        ax, ay = a; bx, by = b; px, py = p
        dx, dy = bx-ax, by-ay
        denom = dx*dx + dy*dy
        if denom == 0:
            return math.hypot(px-ax, py-ay)
        t = max(0, min(1, ((px-ax)*dx + (py-ay)*dy)/denom))
        rx, ry = ax + t*dx, ay + t*dy
        return math.hypot(px-rx, py-ry)

    # récursif
    def _rdp(pts):
        if len(pts) < 3:
            return pts
        a, b = pts[0], pts[-1]
        idx, dmax = 0, -1.0
        for i in range(1, len(pts)-1):
            d = point_line_distance(pts[i], a, b)
            if d > dmax:
                idx, dmax = i, d
        if dmax > epsilon:
            left = _rdp(pts[:idx+1])
            right = _rdp(pts[idx:])
            return left[:-1] + right
        else:
            return [a, b]
    return _rdp(points)

def points_to_path(pts, close_tol=CLOSE_TOL):
    if not pts: return ""
    # force fermeture si début/fin très proches
    closed = dist(pts[0], pts[-1]) < close_tol
    if closed:
        pts = pts[:-1]  # évite de doubler le premier
    parts = [f"M{pts[0][0]:.1f} {pts[0][1]:.1f}"]
    for x, y in pts[1:]:
        parts.append(f"L{x:.1f} {y:.1f}")
    if closed:
        parts.append("Z")
    return " ".join(parts)

# ---------- Exécution ----------
pts = path_to_points(D_PATH)
pts = drop_close_points(pts, EPS)
if TOL > 0:
    pts = rdp(pts, TOL)
d_out = points_to_path(pts)

print("---- d optimisé ----")
print(d_out)

