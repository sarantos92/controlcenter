@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<h1 class="h3 mb-4 text-gray-800">Dashboard</h1>

<div class="row" id="success-message">

</div>

<div class="row">
    <!-- Current rank card  -->
    <div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-primary shadow h-100 py-2">
        <div class="card-body">
        <div class="row no-gutters align-items-center">
            <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase text-gray-600 mb-1">Current Rank</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $data['rating'] }}</div>
            </div>
            <div class="col-auto">
            <i class="fas fa-id-badge fa-2x text-gray-300"></i>
            </div>
        </div>
        </div>
    </div>
    </div>

    <!-- Favorite position card -->
    <div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-primary shadow h-100 py-2">
        <div class="card-body">
        <div class="row no-gutters align-items-center">
            <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase text-gray-600 mb-1">Your favorite position</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800">ENGM_W_APP</div>
            </div>
            <div class="col-auto">
            <i class="fas fa-star fa-2x text-gray-300"></i>
            </div>
        </div>
        </div>
    </div>
    </div>

    <!-- ATC Hours card -->
    <div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-success shadow h-100 py-2">
        <div class="card-body">
        <div class="row no-gutters align-items-center">
            <div class="col mr-2">
            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">ATC Hours (Annual)</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800">245 of 60 hours</div>
            </div>
            <div class="col-auto">
            <i class="fas fa-clock fa-2x text-gray-300"></i>
            </div>
        </div>
        </div>
    </div>
    </div>



    <!-- Last training card -->
    <div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-info shadow h-100 py-2">
        <div class="card-body">
        <div class="row no-gutters align-items-center">
            <div class="col mr-2">
            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Last training</div>
            <div class="row no-gutters align-items-center">
                <div class="col-auto">
                @if ($data['report'] != null) <a href="{{ $data['report']->path() }}"> @endif
                <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $data['report'] != null ? $data['report']->created_at->toFormattedDateString() : "-" }}</div>
                @if ($data['report'] != null) </a> @endif
                </div>
            </div>
            </div>
            <div class="col-auto">
            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
            </div>
        </div>
        </div>
    </div>
    </div>

</div>

<div class="row">
    <!-- Area Chart -->
    <div class="col-xl-8 col-lg-7">
    <div class="card shadow mb-4">
        <!-- Card Header - Dropdown -->
        <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-white">My Trainings</h6>
        </div>
        <!-- Card Body -->
        <div class="card-body p-0">

        @if (sizeof($trainings) == 0)
            You have no trainings.
        @else
        <div class="table-responsive">
            <table class="table table-striped table-hover table-leftpadded mb-0" width="100%" cellspacing="0">
            <thead class="thead-light">
                <tr>
                <th>Level</th>
                <th>Country</th>
                <th>Period</th>
                <th>State</th>
                <th>Reports</th>
                </tr>
            </thead>
            <tbody>
                @foreach($trainings as $training)
                    <tr class="link-row" data-href="{{ $training->path() }}">
                        <td>
                            @foreach($training->ratings as $rating)
                                @if ($loop->last)
                                    {{ $rating->name }}
                                @else
                                    {{ $rating->name . " + " }}
                                @endif
                            @endforeach
                        </td>
                        <td>{{ $training->country->name }}</td>
                        <td>
                            @if ($training->started_at == null && $training->finished_at == null)
                                Training not started
                            @elseif ($training->finished_at == null)
                                {{ $training->started_at->toFormattedDateString() }} -
                            @else
                                {{ $training->started_at->toFormattedDateString() }} - {{ $training->finished_at->toFormattedDateString() }}
                            @endif
                        </td>
                        <td>
                            @switch($training->status)
                                @case(-2)
                                    Closed on student’s request
                                    @break
                                @case(-1)
                                    Closed on TA request
                                    @break
                                @case(0)
                                    In queue
                                    @break
                                @case(1)
                                    In progress
                                    @break
                                @case(2)
                                    Awaiting examination
                                    @break
                                @case(3)
                                    Completed
                                    @break
                            @endswitch
                        </td>
                        <td>
                            <a href="{{ $training->path() }}" class="btn btn-sm btn-primary"><i class="fas fa-clipboard"></i>&nbsp;{{ sizeof($training->reports->toArray()) }}</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            </table>
        </div>
        @endif
        </div>
    </div>
    </div>

    <!-- Pie Chart -->
    <div class="col-xl-4 col-lg-5">
    <div class="card shadow mb-4">
        <!-- Card Header - Dropdown -->
        <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-white">Training</h6>
        </div>
        <!-- Card Body -->
        <div class="card-body">
        <div class="text-center">
            <img class="img-fluid px-3 px-sm-4 mt-3 mb-4" style="width: 25rem;" src="images/undraw_aircraft_fbvl.svg" alt="">
        </div>
        <p>Are you interested in becoming an air traffic controller, or get a higher rank? Here you can request your training, and you will be notified when it's your turn.</p>
        <a href="{{ route('training.apply') }}" class="btn btn-success btn-block">
            Request training
        </a>
        </div>
    </div>
    </div>

</div>
<style>
    .link-row {
        cursor: pointer;
    }
</style>
@endsection

@section('js')
    <script type="text/javascript">

        if (sessionStorage.getItem('successMessage') != null) {
            document.getElementById("success-message").innerHTML = "<div class=\"col-xl-11 col-md-11 mx-auto mb-4 alert alert-success\">\n" +
                "        You training request has been added to the queue!\n" +
                "    </div>";
        }

        $(document).ready( function () {
            setTimeout(function () {
                $("#success-message").css("display", "none");
                sessionStorage.removeItem("successMessage");
            }, 5000);

            $(".link-row").click(function () {
                window.location = $(this).data('href');
            });

        });

    </script>
@endsection
