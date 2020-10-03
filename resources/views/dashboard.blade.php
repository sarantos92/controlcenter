@extends('layouts.app')

@section('title', 'Dashboard')
@section('content')

<!-- Success message fed via JS for TR -->
<div class="row" id="success-message"></div>

<div class="row">
    <!-- Current rating card  -->
    <div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-primary shadow h-100 py-2">
        <div class="card-body">
        <div class="row no-gutters align-items-center">
            <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase text-gray-600 mb-1">Current Rating</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $data['rating'] }}</div>
            </div>
            <div class="col-auto">
            <i class="fas fa-id-badge fa-2x text-gray-300"></i>
            </div>
        </div>
        </div>
    </div>
    </div>

    <!-- Division card -->
    <div class="col-xl-3 col-md-6 mb-4 d-none d-xl-block d-lg-block d-md-block">
    <div class="card border-left-primary shadow h-100 py-2">
        <div class="card-body">
        <div class="row no-gutters align-items-center">
            <div class="col mr-2">
            <div class="text-xs font-weight-bold text-uppercase text-gray-600 mb-1">Your associated division</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800">
                {{ $data['division'] }}/{{ $data['subdivision'] }}
            </div>
            </div>
            <div class="col-auto">
            <i class="fas fa-star fa-2x text-gray-300"></i>
            </div>
        </div>
        </div>
    </div>
    </div>

    <!-- ATC Hours card -->
    <div class="col-xl-3 col-md-6 mb-4 d-none d-xl-block d-lg-block d-md-block">
    <div class="card border-left-success shadow h-100 py-2">
        <div class="card-body">
        <div class="row no-gutters align-items-center">
            <div class="col mr-2">
            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">ATC Hours (Last 12 months)</div>
            <div class="h5 mb-0 font-weight-bold text-gray-800">Coming soon</div>
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
            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">My last training</div>
            <div class="row no-gutters align-items-center">
                <div class="col-auto">
                @if ($data['report'] != null) <a href="{{ $data['report']->training->path() }}"> @endif
                <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $data['report'] != null ? $data['report']->created_at->toEuropeanDate() : "-" }}</div>
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
    <div class="col-xl-8 col-lg-7 ">

    @if(\Auth::user()->isMentor())
        @php
            $student_trainings = \Auth::user()->mentoringTrainings();
        @endphp

            <div class="card shadow mb-4 d-none d-xl-block d-lg-block d-md-block">
                <!-- Card Header - Dropdown -->
                <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-white">My Students</h6>
                </div>
                <!-- Card Body -->
                <div class="card-body {{ sizeof($student_trainings) == 0 ? '' : 'p-0' }}">

                    @if (sizeof($student_trainings) == 0)
                        <p>You have no students.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-leftpadded mb-0" width="100%" cellspacing="0">
                                <thead class="thead-light">
                                <tr>
                                    <th>Student</th>
                                    <th>Level</th>
                                    <th>Country</th>
                                    <th>State</th>
                                    <th>Reports</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($student_trainings as $training)
                                    <tr class="link-row" data-href="{{ $training->path() }}">
                                        <td>{{ $training->user->name }}</td>
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
                                            <i class="{{ $statuses[$training->status]["icon"] }} text-{{ $statuses[$training->status]["color"] }}"></i>&ensp;{{ $statuses[$training->status]["text"] }}{{ isset($training->paused_at) ? ' (PAUSED)' : '' }}
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
    @endif

    <div class="card shadow mb-4">
        <!-- Card Header - Dropdown -->
        <div class="card-header bg-primary py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-white">My Trainings</h6>
        </div>
        <!-- Card Body -->
        <div class="card-body {{ sizeof($trainings) == 0 ? '' : 'p-0' }}">

            @if (sizeof($trainings) == 0)
                <p>You have no registered trainings.</p>
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
                                    @if ($training->started_at == null && $training->closed_at == null)
                                        Training not started
                                    @elseif ($training->closed_at == null)
                                        {{ $training->started_at->toEuropeanDate() }} -
                                    @elseif ($training->stated_at != null)
                                        {{ $training->started_at->toEuropeanDate() }} - {{ $training->closed_at->toEuropeanDate() }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    <i class="{{ $statuses[$training->status]["icon"] }} text-{{ $statuses[$training->status]["color"] }}"></i>&ensp;{{ $statuses[$training->status]["text"] }}{{ isset($training->paused_at) ? ' (PAUSED)' : '' }}
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
                <p>Are you interested in becoming an Air Traffic Controller? Wish to receive training for a higher rating? Request training below and you will be notified when a space is available.</p>

                @can('apply', \App\Training::class)
                    <a href="{{ route('training.apply') }}" class="btn btn-success btn-block">
                        Request training
                    </a>
                @else
                    <a href="#" class="btn btn-primary btn-block disabled" role="button" aria-disabled="true">
                        {{ Gate::inspect('apply', \App\Training::class)->message() }}
                    </a>
                    @if(Setting::get('trainingEnabled'))
                        <div class="alert alert-primary" role="alert">
                            <p><b>FAQ</b></p>
                            <p><b>How do I join the division?</b> <a href="https://vatsim-scandinavia.org/about/join/" target="_blank">Read about joining here.</a><br>
                            <b>How to apply to be a visiting controller?</b> <a href="https://vatsim-scandinavia.org/atc/visiting-controller/" target="_blank">Check this page for more information.</a><br>
                            <b>How long is the queue?</b> {{ Setting::get('trainingQueue') }}
                        </p>
                    @endif
                    </div>
                @endcan
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
