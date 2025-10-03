<?php

namespace App\Controller;

use App\Dto\BulkEmailData;
use App\Form\BulkEmailType;
use App\Repository\MemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Twig\Environment;

#[Route('/board/communication', name: 'app_communication_')]
#[IsGranted('ROLE_COM')]
final class CommunicationController extends AbstractController
{
    #[Route('/email', name: 'email', methods: ['GET', 'POST'])]
    public function email(
        Request $request,
        MemberRepository $memberRepository,
        MailerInterface $mailer,
        Environment $twig,
    ): Response {
        $data = new BulkEmailData();
        $data->from = 'communication@lesfouleespicardes.fr';
        $data->subject = 'Informations Les Foulées Picardes';
        $data->body = "Bonjour {{ member.firstName }},\n\nMerci pour votre engagement avec Les Foulées Picardes.\n\nSportivement,\nLe bureau";

        $form = $this->createForm(BulkEmailType::class, $data);
        $form->handleRequest($request);

        $recipients = $memberRepository->createQueryBuilder('m')
            ->select(
                'm.id AS id',
                'm.firstName AS firstName',
                'm.lastName AS lastName',
                'm.email AS email',
                'm.membershipStatus AS membershipStatus'
            )
            ->andWhere('m.membershipStatus = :status')
            ->andWhere('m.deletedAt IS NULL')
            ->andWhere('m.email IS NOT NULL')
            ->andWhere('m.email <> :empty')
            ->setParameter('status', 'active')
            ->setParameter('empty', '')
            ->orderBy('m.lastName', 'ASC')
            ->addOrderBy('m.firstName', 'ASC')
            ->getQuery()
            ->getArrayResult();

        $previewMember = $recipients[0] ?? null;
        $previewHtml = null;
        if ($previewMember && $data->body) {
            try {
                $previewHtml = $twig->createTemplate($data->body)->render([
                    'member' => $previewMember,
                ]);
            } catch (\Throwable) {
                $previewHtml = null;
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$recipients) {
                $this->addFlash('info', 'Aucun membre actif à contacter pour le moment.');

                return $this->redirectToRoute('app_communication_email');
            }

            try {
                $template = $twig->createTemplate($data->body ?? '');
            } catch (\Throwable $error) {
                $this->addFlash('error', 'Le contenu contient une erreur Twig : '.$error->getMessage());

                return $this->redirectToRoute('app_communication_email');
            }

            $sent = 0;

            foreach ($recipients as $member) {
                try {
                    $html = $template->render(['member' => $member]);
                } catch (\Throwable $error) {
                    $this->addFlash('error', 'Erreur de rendu pour '.$member['email'].' : '.$error->getMessage());

                    return $this->redirectToRoute('app_communication_email');
                }

                $email = (new Email())
                    ->from($data->from)
                    ->to($member['email'])
                    ->subject($data->subject)
                    ->html($html);

                try {
                    $mailer->send($email);
                    ++$sent;
                } catch (TransportExceptionInterface $error) {
                    $this->addFlash('error', 'Envoi interrompu : '.$error->getMessage());

                    return $this->redirectToRoute('app_communication_email');
                }
            }

            $this->addFlash('success', sprintf('Email envoyé à %d membre(s).', $sent));

            return $this->redirectToRoute('app_communication_email');
        }

        return $this->render('communication/email.html.twig', [
            'form' => $form->createView(),
            'recipients' => $recipients,
            'preview_member' => $previewMember,
            'preview_html' => $previewHtml,
        ]);
    }
}
