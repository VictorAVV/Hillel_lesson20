<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Comment
{
    use ORMBehaviors\Timestampable\Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     * @Assert\Length(
     *      min = 3,
     *      max = 2000
     * ) 
     */
    private $content;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $parent_comment;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Article", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $article;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="comments")
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getParentComment(): ?int
    {
        return $this->parent_comment;
    }

    public function setParentComment(?int $parent_comment): self
    {
        $this->parent_comment = $parent_comment;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

        return $this;
    }

    /** @ORM\PrePersist */
    public function setDatetimeForNewComment()
    {   
        $this->setDatetime(new \DateTime());
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /** @ORM\PrePersist */
    public function setDatetimeForNewArticle()
    {   
        $this->setCreatedAt(new \DateTime())->setUpdatedAt(new \DateTime());
    }

    /** @ORM\PreUpdate */
    public function setUpdatedDatetimeForExistsArticle()
    {   
        $this->setUpdatedAt(new \DateTime());
    }
}
