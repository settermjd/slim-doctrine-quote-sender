<?php

namespace AppTest\Data\Fixtures;

use App\Domain\Author;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

class QuoteAuthorDataLoader extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $data = [
            'Anonymous',
            'Brian Kernighan',
        ];

        foreach ($data as $fullName) {
            $author = new Author($fullName);
            $manager->persist($author);
            $manager->flush();

            $this->addReference(
                sprintf('%s-author', str_replace(' ', '-', strtolower($fullName))),
                $author
            );
        }
    }
}