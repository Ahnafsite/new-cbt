<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoomRequest;
use App\Models\Room;
use App\Services\RoomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoomController extends Controller
{
    public function __construct(
        private readonly RoomService $roomService
    ) {
    }

    public function index(Request $request): Response
    {
        $rooms = $this->roomService->list($request->only(['search', 'per_page']));

        return Inertia::render('Admin/Rooms/Index', [
            'rooms' => $rooms,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(RoomRequest $request): RedirectResponse
    {
        $this->roomService->store($request->validated());

        return redirect()->back()->with('success', 'Ruangan berhasil ditambahkan.');
    }

    public function update(RoomRequest $request, Room $room): RedirectResponse
    {
        $this->roomService->update($room, $request->validated());

        return redirect()->back()->with('success', 'Ruangan berhasil diperbarui.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        $this->roomService->delete($room);

        return redirect()->back()->with('success', 'Ruangan berhasil dihapus.');
    }
}
