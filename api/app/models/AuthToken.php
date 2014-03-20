<?php
namespace models;

/**
 * AuthToken
 *
 * @author Endel Dreyer <endel.dreyer@gmail.com>
 */
class AuthToken extends \Core\Model {

	const EXPIRATION = 24; // hours

	protected $guarded = array();
	protected $primaryKey = '_id';
	public $timestamps = false;

	public static function boot() {
		static::saving(function($model) { $model->beforeSave(); });
	}

	public function app() {
		return $this->belongsTo('models\App');
	}

	public function auth() {
		return $this->belongsTo('models\Auth');
	}

	public function beforeSave() {
		$this->expire_at = time() + (static::EXPIRATION * 60 * 60);
		$this->created_at = time();
		$this->token = md5(uniqid(rand(), true));
		// $this->level = 1;
	}

}
