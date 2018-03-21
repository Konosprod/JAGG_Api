<?php

require_once "map.php";

class Author extends Illuminate\Database\Eloquent\Model {
	protected $table = "authors";

	protected $hidden = ["steamid", "created_at", "updated_at"];

	public function maps() {
		return $this->hasMany(Map::class);
	}
}
