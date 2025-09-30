<?php

namespace App\DataFixtures;

use App\Entity\Member;
use App\Entity\MembershipRequest;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    /**
     * @var Generator
     */
    private Generator $faker;

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->faker = Factory::create('fr_FR');

        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $admin = $manager->find(Member::class, 16);

        for ($i = 0; $i < 5; ++$i) {
            $member = new Member();
            $member->setEmail($this->faker->email());
            $member->setPassword($this->passwordHasher->hashPassword($member, $this->faker->password()));
            $member->setFirstName($this->faker->firstName());
            $member->setLastName($this->faker->lastName());
            $member->setAddress($this->faker->address());
            $member->setDateOfBirth(new \DateTime($this->faker->date()));
            $member->setPhone($this->faker->phoneNumber());
            $member->setMembershipStatus('pending');
            $member->setCreatedAt(new \DateTimeImmutable());

            $memberShipRequest = new MembershipRequest();
            $memberShipRequest->setValidatedBy($admin);
            $memberShipRequest->setRequester($member);
            $memberShipRequest->setMessage($this->faker->text());
            $memberShipRequest->setRgpdAccepted(true);
            $memberShipRequest->setCreatedAt(new \DateTimeImmutable());
            $memberShipRequest->setUpdatedAt(new \DateTimeImmutable());
            $memberShipRequest->setVerificationTokenHash($this->faker->linuxPlatformToken());
            $memberShipRequest->setIsEmailVerified(true);
            $memberShipRequest->setEmailVerifiedAt(new \DateTimeImmutable());


            $manager->persist($memberShipRequest);
            $manager->persist($member);
        }

        $manager->flush();
    }
}
