<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexReservationsRequest;
use App\Http\Requests\Admin\StoreReservationRequest;
use App\Http\Requests\Admin\UpdateReservationStatusRequest;
use App\Models\LessonSchedule;
use App\Models\Reservation;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ReservationController extends Controller
{
    public function index(IndexReservationsRequest $request): View
    {
        $query = Reservation::query()
            ->with(['user', 'lessonSchedule.lesson', 'userSubscription.plan'])
            ->latest('reserved_at');

        $v = $request->validated();

        if (! empty($v['user_id'])) {
            $query->where('user_id', (int) $v['user_id']);
        }
        if (! empty($v['lesson_schedule_id'])) {
            $query->where('lesson_schedule_id', (int) $v['lesson_schedule_id']);
        }
        if (! empty($v['status'])) {
            $query->where('status', $v['status']);
        }
        // Filter by reservation booked date
        $from = ! empty($v['reserved_from']) ? \Illuminate\Support\Carbon::parse($v['reserved_from'])->startOfDay() : null;
        $to = ! empty($v['reserved_to']) ? \Illuminate\Support\Carbon::parse($v['reserved_to'])->endOfDay() : null;
        if ($from) {
            $query->where('reserved_at', '>=', $from);
        }
        if ($to) {
            $query->where('reserved_at', '<=', $to);
        }

        $reservations = $query->paginate(15)->withQueryString();

        $users = User::query()->orderBy('name')->get(['id', 'name']);
        $schedules = LessonSchedule::query()->latest('start_datetime')->limit(50)->get(['id', 'lesson_id', 'start_datetime']);

        return view('admin.reservations.index', compact('reservations', 'users', 'schedules'));
    }

    public function create(): View
    {
        $users = User::query()->orderBy('name')->get(['id', 'name']);
        $schedules = LessonSchedule::query()->with('lesson')->latest('start_datetime')->limit(100)->get();

        return view('admin.reservations.create', compact('users', 'schedules'));
    }

    public function store(StoreReservationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $result = Reservation::createWithValidation([
            'user_id' => (int) $data['user_id'],
            'lesson_schedule_id' => (int) $data['lesson_schedule_id'],
            'user_subscription_id' => (int) $data['user_subscription_id'],
            'reserved_at' => now(),
        ]);

        if (! ($result['success'] ?? false)) {
            $errors = (array) ($result['errors'] ?? [trans('reservation.errors.generic_failure')]);

            return back()->withErrors($errors)->withInput();
        }

        return redirect()->route('admin.reservations.index')
            ->with('status', trans('reservation.success.created'));
    }

    public function show(Reservation $reservation): View
    {
        $reservation->load(['user', 'lessonSchedule.lesson', 'userSubscription.plan']);

        return view('admin.reservations.show', compact('reservation'));
    }

    public function edit(Reservation $reservation): View
    {
        $reservation->load(['user', 'lessonSchedule.lesson', 'userSubscription.plan']);

        return view('admin.reservations.edit', compact('reservation'));
    }

    public function update(UpdateReservationStatusRequest $request, Reservation $reservation): RedirectResponse
    {
        $status = $request->validated()['status'];

        // Supported transitions:
        // - confirmed -> canceled: use domain cancelWithValidation (adjusts counts)
        // - confirmed -> completed: simple state update
        // - confirmed -> no_show: simple state update
        // Other transitions are restricted for safety.

        if ($status === Reservation::STATUS_CANCELED) {
            $result = $reservation->cancelWithValidation();
            if (! ($result['success'] ?? false)) {
                $error = (string) ($result['error'] ?? trans('reservation.errors.cancel_generic_failure'));

                return back()->withErrors([$error]);
            }

            return redirect()->route('admin.reservations.index')
                ->with('status', trans('reservation.success.canceled'));
        }

        if (in_array($status, [Reservation::STATUS_COMPLETED, Reservation::STATUS_NO_SHOW], true)) {
            $reservation->status = $status;
            $reservation->save();

            return redirect()->route('admin.reservations.index')
                ->with('status', trans('reservation.success.updated'));
        }

        return back()->withErrors([trans('reservation.errors.status_update_not_supported')]);
    }

    public function destroy(Reservation $reservation): RedirectResponse
    {
        if ($reservation->status !== Reservation::STATUS_CANCELED) {
            return back()->withErrors([trans('reservation.errors.delete_only_canceled')]);
        }

        $reservation->delete();

        return redirect()->route('admin.reservations.index')
            ->with('status', trans('reservation.success.deleted'));
    }
}
