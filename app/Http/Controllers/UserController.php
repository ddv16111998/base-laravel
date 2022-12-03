<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\UserRepositoryInterface;

class UserController extends Controller
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        $users = $this->userRepository->where('email', 'viendd@vmodev.com')->first();
        dd($users);
    }
}
