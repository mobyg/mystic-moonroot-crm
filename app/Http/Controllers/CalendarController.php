<?php
// app/Http/Controllers/CalendarController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $view = $request->get('view', 'month');
        $date = $request->get('date', now()->format('Y-m-d'));
        $currentDate = Carbon::parse($date);
        
        $events = Event::where('user_id', auth()->id())
                      ->whereBetween('start_datetime', [
                          $currentDate->copy()->startOfMonth()->subWeeks(1),
                          $currentDate->copy()->endOfMonth()->addWeeks(1)
                      ])
                      ->get();
        
        return view('calendar.index', compact('events', 'view', 'currentDate'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'required|date',
            'end_datetime' => 'nullable|date|after:start_datetime',
            'comment' => 'nullable|string'
        ]);

        $event = Event::create([
            'title' => $request->title,
            'description' => $request->description,
            'start_datetime' => $request->start_datetime,
            'end_datetime' => $request->end_datetime ?? $request->start_datetime,
            'comment' => $request->comment,
            'user_id' => auth()->id()
        ]);

        return response()->json(['success' => true, 'event' => $event]);
    }

    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'required|date',
            'end_datetime' => 'nullable|date|after:start_datetime',
            'comment' => 'nullable|string'
        ]);

        $event->update([
            'title' => $request->title,
            'description' => $request->description,
            'start_datetime' => $request->start_datetime,
            'end_datetime' => $request->end_datetime ?? $request->start_datetime,
            'comment' => $request->comment
        ]);

        $redirectTo = auth()->user()->event_redirect === 'visit_updated' 
            ? ['date' => Carbon::parse($request->start_datetime)->format('Y-m-d')]
            : [];

        return response()->json([
            'success' => true, 
            'event' => $event, 
            'redirect' => $redirectTo
        ]);
    }

    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);
        $event->delete();
        
        return response()->json(['success' => true]);
    }

    public function move(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $request->validate([
            'start_datetime' => 'required|date'
        ]);

        $duration = Carbon::parse($event->end_datetime)->diffInMinutes(Carbon::parse($event->start_datetime));
        $newStart = Carbon::parse($request->start_datetime);
        $newEnd = $newStart->copy()->addMinutes($duration);

        $event->update([
            'start_datetime' => $newStart,
            'end_datetime' => $newEnd
        ]);

        return response()->json(['success' => true, 'event' => $event]);
    }

    public function getEvents(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');
        
        $events = Event::where('user_id', auth()->id())
                      ->whereBetween('start_datetime', [$start, $end])
                      ->get()
                      ->map(function ($event) {
                          return [
                              'id' => $event->id,
                              'title' => $event->title,
                              'start' => $event->start_datetime->toISOString(),
                              'end' => $event->end_datetime->toISOString(),
                              'description' => $event->description,
                              'comment' => $event->comment
                          ];
                      });

        return response()->json($events);
    }
}