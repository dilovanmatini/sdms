<?php

namespace App\Http\Controllers\Settings;

use App\Authorization\Ability;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $cards = [
            [
                'title' => 'عام',
                'description' => 'اسم التطبيق والشعار ورؤوس وتذييلات المستندات',
                'href' => route('settings.general.edit'),
                'ability' => Ability::ManageSettings->value,
            ],
            [
                'title' => 'وحدات القياس',
                'description' => 'إدارة وحدات القياس المستخدمة في المنتجات',
                'href' => route('units.index'),
                'ability' => Ability::ManageUnits->value,
            ],
            [
                'title' => 'المستخدمون',
                'description' => 'إدارة حسابات النظام والأدوار',
                'href' => route('users.index'),
                'ability' => Ability::ManageUsers->value,
            ],
        ];

        $visibleCards = array_values(array_filter(
            $cards,
            static fn (array $card): bool => $card['ability'] === null
                || ($user !== null && $user->hasAbility($card['ability'])),
        ));

        return Inertia::render('settings/index', [
            'cards' => $visibleCards,
        ]);
    }
}
