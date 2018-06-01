<?php

declare(strict_types = 1);


namespace App\Model;


use Dibi\Fluent;


class PersonModel extends BaseModel
{
	public function getFluentBuilder() : Fluent
	{
		return $this->database->select('*')->from('persons');
	}
}