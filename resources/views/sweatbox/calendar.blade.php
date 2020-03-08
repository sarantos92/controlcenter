@extends('layouts.app')

@section('title', 'Sweatbox Calendar')

@section('content')
<h1 class="h3 mb-4 text-gray-800">Sweatbox Calendar</h1>

<div class="row">
    <div class="col-xl-12 col-md-12 mb-12">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-white">Booked Sessions</h6> 
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-sm table-hover table-leftpadded mb-0" width="100%" cellspacing="0"
                        data-toggle="table"
                        data-pagination="true"
                        data-strict-search="true"
                        data-filter-control="true">
                        <thead class="thead-light">
                            <tr>
                                <th data-sortable="true" data-filter-control="select">Date</th>
                                <th data-filter-control="select">Start (Zulu)</th>
                                <th data-filter-control="select">End (Zulu)</th>
                                <th data-sortable="true" data-filter-control="select">Position</th>
                                <th data-sortable="true" data-filter-control="select">Mentor</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                            @foreach($bookings as $booking)
                            <tr>
                                <td> 
                                    @if ($booking->mentor == $user->id || $user->isModerator())
                                        <a href="/sweatbox/{{ $booking->id }}">{{ date('F d, Y', strtotime($booking->date)) }}</a>
                                    @else
                                        {{ date('F d, Y', strtotime($booking->date)) }}
                                    @endif
                                    
                                </td>
                                <td>
                                    {{ date('H:i', strtotime($booking->start_at)) }}z
                                </td>
                                <td>
                                    {{ date('H:i', strtotime($booking->end_at)) }}z
                                </td>
                                <td>
                                    {{ $booking->position }}
                                </td>
                                <td>
                                    @if ( sizeof($user->handover->where('id', '=', $booking->mentor)->get()) < 1 )
                                        Invalid User
                                    @else
                                        {{ $user->handover->where('id', '=', $booking->mentor)->get()[0]->firstName }} {{ $user->handover->where('id', '=', $booking->mentor)->get()[0]->lastName }} ({{ $booking->mentor }})
                                    @endif
                                </td>
                                <td>
                                    {{ mb_strimwidth($booking->mentor_notes, 0, 40) }}
                                </td>
                            </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
        <div class="align-items-left">
            <a href="{{ route('sweatbox.create') }}" class="btn btn-success">Add Booking</a>
        </div>
    </div>
    
</div>

@endsection

@section('js')
<script>
    //Activate bootstrap tooltips
    $(document).ready(function() {
        $('div').tooltip();
    })
</script>
@endsection