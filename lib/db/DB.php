<?php
namespace lib\db;

interface db
{
	
	public function insert($data);
	public function multiInsert();
	public function delete();
	public function select();
	public function update();
}