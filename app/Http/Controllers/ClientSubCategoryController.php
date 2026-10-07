<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\ClientSubCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Add this line to import DB facade
use Carbon\Carbon;

class ClientSubCategoryController extends Controller
{
    public function index()
    {
        return ClientSubCategory::with('category')->get();
    }
    
    public function store(Request $request)
    {
        $connectionName = (new ClientSubCategory)->getConnectionName();
        $schema = (new ClientSubCategory)->getConnection()->getSchemaBuilder();

        if ($schema->hasTable('client_sub_categories') && ! $schema->hasColumn('client_sub_categories', 'name')) {
            $schema->table('client_sub_categories', function ($table) {
                $table->string('name')->nullable();
            });
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique($connectionName . '.client_sub_categories', 'name'),
            ],
            'client_category_id' => [
                'required',
                \Illuminate\Validation\Rule::exists($connectionName . '.client_categories', 'id'),
            ],
        ]);
        $sub = ClientSubCategory::create($request->only('name', 'client_category_id'));
        return response()->json($sub->load('category'));
    }
}
