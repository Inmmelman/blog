<?php
namespace Entities;

/**
 * @Entity
 * @Table(name="posts")
 */
class Post {


    /**
     * @id @Column(type="integer", nullable=false, options={"unsigned" = true})
     * @GeneratedValue
     */
    private $id;

    /** @Column(type="text") */
    private $content;

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param mixed $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return mixed
	 */
	public function getContent($length = null)
	{
		 if (false === is_null($length) && $length > 0)
	        return substr($this->content, 0, $length);
	    else
	        return $this->content;
	}

	/**
	 * @param mixed $content
	 */
	public function setContent($content)
	{
		$this->content = $content;
	}



}