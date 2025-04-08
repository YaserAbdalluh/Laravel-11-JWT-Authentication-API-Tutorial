<?php

namespace App\GraphQL\Queries;

// use Illuminate\Support\Facades\Auth;

use App\Models\Profession;

class ProfessionQuery
{
    public function resolve($root, array $args)
    {
        $query = Profession::query();

        // Apply filters if they exist
        if (isset($args['name'])) {
            $query->where('name', 'LIKE', '%' . $args['name'] . '%');
        }

        if (isset($args['category_id'])) {
            $query->where('category_id', $args['category_id']);
        }
        // Return paginated results (10 per page by default)
        return $query->get();
    }
}
