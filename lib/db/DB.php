<?php
namespace lib\db;

interface db
{
	
	public function insert($data);
	public function multiInsert($data, $options);
	public function delete($where, $options);
	public function select($where);
	public function update($where,$data,$options);
}