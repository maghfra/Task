<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function index()
    {
        $cacheKey = 'stats'; 

        $stats = Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return [
                'total_users' => User::count(),
                'total_tags' => Tag::count(),
                'total_posts' => Post::count(),
                'users_with_zero_posts' => User::doesntHave('posts')->count(),
            ];
        });

        return response()->json([
            'message' => 'Statistics retrieved successfully',
            'data' => $stats
        ], 200);
    }
    }
