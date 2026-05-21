<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMemberHasProject
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if ($user->role !== User::ROLE_MEMBER) {
            return $next($request);
        }

        $has_project = $user->projects()->exists();

        if ($has_project) {
            return $next($request);
        }

        $is_allowed_path = $request->is('admin/chua-duoc-phan-vao-du-an')
            || $request->is('admin/logout')
            || $request->is('livewire/*');

        if ($is_allowed_path) {
            return $next($request);
        }

        return redirect('/admin/chua-duoc-phan-vao-du-an');
    }
}
