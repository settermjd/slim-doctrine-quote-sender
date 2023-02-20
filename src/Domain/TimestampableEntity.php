<?php

declare(strict_types=1);

namespace App\Domain;

use Doctrine\ORM\Mapping as ORM;

trait TimestampableEntity
{
    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
    protected ?\DateTime $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    protected ?\DateTime $updatedAt = null;

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updatedTimestamps()
    {
        $this->setUpdatedAt(new \DateTime('now'));

        if ($this->createdAt === null) {
            $this->setCreatedAt(new \DateTime('now'));
        }
    }
}