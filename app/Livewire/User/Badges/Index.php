<?php

namespace App\Livewire\User\Badges;

use App\Enums\BadgeCategory;
use App\Models\Badge;
use Livewire\Component;

class Index extends Component
{
    public string $filterCategory = '';

    public function render()
    {
        $allBadges = Badge::active()->ordered()->get();
        $earnedBadgeIds = auth()->user()->badges()->pluck('badges.id')->toArray();

        // Filtra per categoria se selezionata
        if ($this->filterCategory) {
            $allBadges = $allBadges->where('category', BadgeCategory::from($this->filterCategory));
        }

        // Separa badge vinti e da vincere
        $earnedBadges = $allBadges->filter(fn($b) => in_array($b->id, $earnedBadgeIds));
        $lockedBadges = $allBadges->filter(fn($b) => !in_array($b->id, $earnedBadgeIds));

        // Prendi le date di assegnazione
        $earnedDates = auth()->user()->badges()
            ->pluck('user_badges.earned_at', 'badges.id')
            ->toArray();

        return view('livewire.user.badges.index', [
            'earnedBadges' => $earnedBadges,
            'lockedBadges' => $lockedBadges,
            'earnedDates' => $earnedDates,
            'categories' => BadgeCategory::cases(),
            'totalEarned' => count($earnedBadgeIds),
            'totalBadges' => Badge::active()->count(),
        ]);
    }
}
