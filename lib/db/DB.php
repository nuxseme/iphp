<?php
namespace lib\db;

interface db
{
	
	public function insert($data);
	public function multiInsert();
	public function delete($where);
	public function select($where);
	public function update($where,$data,$options);
}