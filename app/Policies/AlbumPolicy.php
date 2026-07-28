<?php

namespace App\Policies;

use App\Models\Album;
use App\Models\User;

class AlbumPolicy
{
    public function update(User $user, Album $album): bool { return $album->user_id === $user->id; }
    public function delete(User $user, Album $album): bool { return $album->user_id === $user->id; }
}
