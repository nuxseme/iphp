<?php
namespace lib\db;

interface DB
{
	public function insert();
	public function multiInsert();
	public function delete();
	public function select();
	public function update();
}