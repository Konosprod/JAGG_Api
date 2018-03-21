<?php

require_once "tag.php";

class Map extends Illuminate\Database\Eloquent\Model {
	protected $table = "maps";

	protected $with = ["tags", "author"];
	protected $hidden = ["pivot", "author_id"];


	public function getTagsIdsAttribute() {
		return $this->tags->modelKeys();
	}

	public function tags() {
		return $this->belongsToMany(Tag::class, "tags_maps");
	}

	public function author() {
		return $this->belongsTo(Author::class);
	}
}
