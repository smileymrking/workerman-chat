<?php
/**
 * Created by PhpStorm.
 * User: King
 * Date: 2019/7/23
 * Time: 17:24
 */

namespace App\Services;


use App\Models\User\User;

class AuthService
{
    /**
     * @var User
     */
    private $user;

    /**
     * AuthService constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|mixed
     * @author: King
     * @version: 2019/7/23 17:47
     */
    public function createUser(array $data)
    {
        return $this->user->newQuery()->create($data);
    }

    /**
     * @param array $data
     * @return bool
     * @author: King
     * @version: 2019/7/26 16:06
     */
    public function userExists(array $data)
    {
        return $this->user->newQuery()->where($data)->exists();
    }
}
