<?php
namespace Hook\Model;

/**
 * Auth
 *
 * @uses Collection
 */
class AuthIdentity extends Model
{
    protected $table = 'auth_identities';

    public function auth()
    {
        return $this->belongsTo('Hook\Model\Auth', 'auth_id');
    }

}
