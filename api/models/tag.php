<?php

require_once "map.php";

class Tag extends Illuminate\Database\Eloquent\Model {
	protected $table = "tags";

	protected $hidden = ["pivot", "created_at", "updated_at"];

	public function maps() {
		return $this->belongsToMany(Map::class, "tags_maps");
	}
}
