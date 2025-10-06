<?php

namespace App\Livewire;

use App\Models\LessonSchedule;
use App\Models\Reservation;
use App\Models\Store;
use App\Models\User;
use App\Models\UserFavorite;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ReservationBooking extends Component
{
    /**
     * Mode of the component: 'group' or 'personal'.
     */
    public string $mode = 'group';

    /**
     * Selected date in Y-m-d.
     */
    public string $date;

    /**
     * Active tab for selectors: 'favorites' or 'all'.
     */
    public string $tab = 'all';

    /**
     * Selected store IDs for filtering (group mode).
     *
     * @var array<int>
     */
    public array $selectedStoreIds = [];

    /**
     * Selected instructor (user) IDs for filtering (personal mode).
     *
     * @var array<int>
     */
    public array $selectedInstructorIds = [];

    /**
     * Time slot filter: 'all' | 'morning' | 'afternoon' | 'evening'.
     */
    public string $timeSlot = 'all';

    /**
     * Initialize component state.
     */
    public function mount(string $mode = 'group', ?string $date = null): void
    {
        $mode = in_array($mode, ['group', 'personal'], true) ? $mode : 'group';
        $this->mode = $mode;
        $this->date = $date ?: now()->toDateString();

        // 初期表示で当日に予定がない場合は、今後の最初のスケジュール日へ自動ジャンプ
        if (! $this->hasSchedulesForDate($this->date)) {
            $next = LessonSchedule::query()
                ->active()
                ->where('start_datetime', '>=', now())
                ->orderBy('start_datetime')
                ->first();

            if ($next?->start_datetime) {
                $this->date = $next->start_datetime->toDateString();
            }
        }
    }

    /**
     * Switch current date by +N / -N days.
     */
    public function jumpDays(int $days): void
    {
        $this->date = Carbon::parse($this->date)->addDays($days)->toDateString();
    }

    /**
     * Toggle a store id in the selection list (group mode).
     */
    public function toggleStoreId(int $storeId): void
    {
        $storeId = (int) $storeId;
        if (in_array($storeId, $this->selectedStoreIds, true)) {
            $this->selectedStoreIds = array_values(array_filter(
                $this->selectedStoreIds,
                fn ($id) => (int) $id !== $storeId
            ));
        } else {
            $this->selectedStoreIds[] = $storeId;
        }
    }

    /**
     * Toggle an instructor id in the selection list (personal mode).
     */
    public function toggleInstructorId(int $instructorId): void
    {
        $instructorId = (int) $instructorId;
        if (in_array($instructorId, $this->selectedInstructorIds, true)) {
            $this->selectedInstructorIds = array_values(array_filter(
                $this->selectedInstructorIds,
                fn ($id) => (int) $id !== $instructorId
            ));
        } else {
            $this->selectedInstructorIds[] = $instructorId;
        }
    }

    /**
     * 指定日のスケジュールが存在するか判定
     */
    protected function hasSchedulesForDate(string $date): bool
    {
        return LessonSchedule::query()
            ->active()
            ->whereDate('start_datetime', $date)
            ->exists();
    }

    /**
     * Get the authenticated user instance.
     */
    protected function authUser(): ?Authenticatable
    {
        return auth()->user();
    }

    /**
     * Favorite stores for the current user (ids and models).
     *
     * @return Collection<int, Store>
     */
    #[Computed]
    public function favoriteStores(): Collection
    {
        $userId = (int) ($this->authUser()?->getAuthIdentifier() ?? 0);
        if ($userId === 0) {
            return collect();
        }

        $ids = UserFavorite::query()
            ->where('user_id', $userId)
            ->stores()
            ->pluck('favoritable_id');

        return Store::query()
            ->whereIn('id', $ids)
            ->active()
            ->orderBy('name')
            ->get();
    }

    /**
     * Favorite instructors for the current user.
     *
     * @return Collection<int, User>
     */
    #[Computed]
    public function favoriteInstructors(): Collection
    {
        $userId = (int) ($this->authUser()?->getAuthIdentifier() ?? 0);
        if ($userId === 0) {
            return collect();
        }

        $ids = UserFavorite::query()
            ->where('user_id', $userId)
            ->instructors()
            ->pluck('favoritable_id');

        return User::query()
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get();
    }

    /**
     * Time boundaries for the selected time slot on the selected date.
     *
     * @return array{start: \DateTimeInterface, end: \DateTimeInterface}|
     *         array{start: null, end: null}
     */
    protected function timeSlotRange(): array
    {
        $date = Carbon::parse($this->date);

        return match ($this->timeSlot) {
            'morning' => ['start' => $date->copy()->setTime(6, 0), 'end' => $date->copy()->setTime(12, 0)],
            'afternoon' => ['start' => $date->copy()->setTime(12, 0), 'end' => $date->copy()->setTime(18, 0)],
            'evening' => ['start' => $date->copy()->setTime(18, 0), 'end' => $date->copy()->endOfDay()],
            default => ['start' => null, 'end' => null],
        };
    }

    /**
     * Query available schedules for the selected filters.
     *
     * @return Collection<int, LessonSchedule>
     */
    #[Computed]
    public function schedules(): Collection
    {
        $userId = (int) ($this->authUser()?->getAuthIdentifier() ?? 0);

        $base = LessonSchedule::query()
            ->with(['lesson.store', 'lesson.category', 'lesson.instructor'])
            ->withExists([
                'reservations as reserved_by_me' => function ($q) use ($userId): void {
                    $q->where('user_id', $userId)
                        ->where('status', Reservation::STATUS_CONFIRMED);
                },
            ])
            ->active()
            ->where('start_datetime', '>=', now())
            ->orderBy('start_datetime');

        // Root category names
        $rootGroupName = 'グループレッスン';
        $rootPersonalName = 'パーソナルレッスン';

        // Filter by root category depending on mode
        if ($this->mode === 'group') {
            $base->whereHas('lesson.category', function ($q) use ($rootGroupName): void {
                $q->where(function ($qq) use ($rootGroupName) {
                    $qq->whereNull('parent_id')->where('name', $rootGroupName)
                        ->orWhereHas('parent', function ($p) use ($rootGroupName) {
                            $p->where('name', $rootGroupName);
                        });
                });
            });
        } else {
            $base->whereHas('lesson.category', function ($q) use ($rootPersonalName): void {
                $q->where(function ($qq) use ($rootPersonalName) {
                    $qq->whereNull('parent_id')->where('name', $rootPersonalName)
                        ->orWhereHas('parent', function ($p) use ($rootPersonalName) {
                            $p->where('name', $rootPersonalName);
                        });
                });
            });
        }

        if ($this->mode === 'group') {
            $limit = 15;
            $favStoreIds = $this->favoriteStores()->pluck('id')->all();

            if (! empty($favStoreIds)) {
                $favQuery = (clone $base)->whereHas('lesson', function ($q) use ($favStoreIds): void {
                    $q->whereIn('store_id', $favStoreIds);
                });
                $fav = $favQuery->limit($limit)->get();
                if ($fav->isNotEmpty()) {
                    return $fav;
                }
            }

            return (clone $base)->limit($limit)->get();
        }

        // personal mode (従来のフィルタを維持)
        $query = (clone $base);

        if ($this->tab === 'favorites') {
            $favInstructorIds = $this->favoriteInstructors()->pluck('id')->all();
            if (! empty($favInstructorIds)) {
                $query->whereHas('lesson', function ($q) use ($favInstructorIds): void {
                    $q->whereIn('instructor_user_id', $favInstructorIds);
                });
            }
        }

        if (! empty($this->selectedInstructorIds)) {
            $ids = array_map('intval', $this->selectedInstructorIds);
            $query->whereHas('lesson', function ($q) use ($ids): void {
                $q->whereIn('instructor_user_id', $ids);
            });
        }

        return $query->limit(15)->get();
    }

    /**
     * Group schedules by time bucket label (e.g., 06:00, 07:00...).
     *
     * @return Collection<string, Collection<int, LessonSchedule>>
     */
    #[Computed]
    public function schedulesByTime(): Collection
    {
        return $this->schedules->groupBy(function (LessonSchedule $s): string {
            return $s->start_datetime?->format('H:00') ?? '未定';
        });
    }

    /**
     * Render the component.
     */
    public function render(): ViewContract
    {
        return view('livewire.reservation-booking');
    }
}
