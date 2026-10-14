<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\ClientCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Add this line to import DB facade
use Carbon\Carbon;

class ClientCategoryController extends Controller
{
    public function index()
    {
        return ClientCategory::orderBy('name')->get();
    }
        
    public function store(Request $request)
    {
        $connectionName = (new ClientCategory)->getConnectionName();
        $schema = (new ClientCategory)->getConnection()->getSchemaBuilder();

        if ($schema->hasTable('client_categories') && ! $schema->hasColumn('client_categories', 'name')) {
            $schema->table('client_categories', function ($table) {
                $table->string('name')->nullable();
            });
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique($connectionName . '.client_categories', 'name'),
            ],
        ]);

        $category = ClientCategory::create([
            'name' => trim((string) $request->name),
        ]);

        return response()->json($category);
    }
}
