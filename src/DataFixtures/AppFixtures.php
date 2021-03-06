<?php

namespace App\DataFixtures;

use App\Entity\Ad;
use Faker\Factory;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\Image;
use App\Entity\Booking;
use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    
    private $encoder;

    //constructor so we can use $encoder
    public function __construct(UserPasswordEncoderInterface $encoder){
        $this->encoder = $encoder;
    }


    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('FR-fr');

        $adminRole = new Role();
        $adminRole->setTitle('ROLE_ADMIN');
        $manager->persist($adminRole);

        $adminUser = new User();
        $adminUser->setEmail('admin@admin.com')
                  ->setFirstName('John')
                  ->setHash($this->encoder->encodePassword($adminUser, 'admin'))
                  ->setIntroduction('Le dev')
                  ->setLastName('K......')
                  ->setPicture('https://avatars3.githubusercontent.com/u/36775219?s=400&u=b2ed2bee07d88192f891abee56ee4417d08cb6f8&v=4')
                  ->setTel('0600000000')
                  ->setText('pas de description')
                  ->addUserRole($adminRole);
        $manager->persist($adminUser);




        /////// USERS //////
        $users = [];
        $genders = ['male', 'female']; //in order to get logical pictures and names acording to ones gender.

        for($i=1; $i<=10; $i++){
            $user = new User();
            $gender = $faker->randomElement($genders);
            $picture='https://randomuser.me/api/portraits/'; // random avatar pics website, very handy!
            $picture = $picture . ($gender == 'male' ? 'men/' : 'women/') . mt_rand(1, 99) . '.jpg';

            $hash = $this->encoder->encodePassword($user, 'password');
            
            $user -> setFirstName($faker->firstname($gender))
                    -> setLastName($faker->lastname)
                    -> setEmail($faker->email)
                    -> setHash($hash)
                    -> setIntroduction($faker->sentence())
                    -> setTel($faker->e164PhoneNumber)
                    ->setPicture($picture)
                    -> setText('RANDOM TEXT HERE');
                    

            $manager->persist($user);
            $users[]=$user;
        }


        ////// ADS //////
        for ($i=1; $i<=30; $i++){

        $title = $faker->sentence();
        $intro = $faker->paragraph(2);
        $content = '<p>'. join('</p><p>', $faker->paragraphs(5)) . '</p>';
        $coverImage = $faker->imageUrl(1000, 350);

        $startAdDate = $faker->dateTimeBetween($startDate = '-2 months', $endDate = '+3 months');
        $duration    = mt_rand(15, 40);
        $endAdDate   = (clone $startAdDate)->modify("+$duration days"); 

        $user = $users[mt_rand(0, count($users)-1)];

        $ad = new Ad();
        $ad -> setTitle($title)
            ->setHoursPerDay(\mt_rand(1, 6))
            ->setIntroduction($intro)
            ->setContent($content)
            ->setCoverImage($coverImage)
            ->setAuthor($user)
            ->setStartAdDate($startAdDate)
            ->setEndAdDate($endAdDate)
            ->setDaysPerMission(\mt_rand(1,6));

            for($j=1; $j<= mt_rand(2,5); $j++){
                $image = new Image();

                $image->setUrl($faker->imageUrl())
                    ->setCaption($faker->sentence())
                    ->setAd($ad);
                    $manager->persist($image);
            }
            ////BOOKINGS////
            for($j = 1; $j <= mt_rand(0,10); $j++){
                $booking = new Booking();

                $createdAt = $faker->dateTimeBetween('-6 months');
                $startDate = $faker->dateTimeBetween('-3 months');

                $duration  = mt_rand(3, 10);
                $endDate   = (clone $startDate)->modify("+$duration days");
                $amount    = $ad->getHoursPerDay() * $duration;

                $booker    = $users[mt_rand(0, count($users) -1)];
                $comment   = $faker->paragraph();

                $booking->setAd($ad)
                        ->setAmount($amount)
                        ->setBooker($booker)
                        ->setCreatedAt($createdAt)
                        ->setEndDate($endDate)
                        ->setComment($comment)
                        ->setStartDate($startDate);
                       
                $manager->persist($booking);

                ////COMMENTS/////
                if(mt_rand(0, 1)){
                    $comment = new Comment();
                    $comment->setContent($faker->paragraph())
                            ->setRating(mt_rand(0, 5))
                            ->setAuthor($booker)
                            ->setAd($ad);
                    $manager->persist($comment);
                }
            }



        $manager->persist($ad);
    }

        $manager->flush();
    }
}
