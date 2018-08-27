@extends('admin.default')

@section('content')

    <div class="row gap-20 masonry pos-r">
        <div class="masonry-sizer col-md-6"></div>
        <div class="masonry-item col-12">
            <!-- Get sellerdashboards and overall feedback from each ecommerce platform
                 and color the country or zone of the feedback / or the use -->
            <!-- #Site Visits ==================== -->
            <div class="bd bgc-white">
                <div class="peers fxw-nw@lg+ ai-s">
                    <div class="peer peer-greed w-70p@lg+ w-100@lg- p-20">
                        <div class="layers">
                            <div class="layer w-100 mB-10">
                                <h6 class="lh-1">Site Visits</h6>
                            </div>
                            <div class="layer w-100">
                                <div id="world-map-marker"></div>
                            </div>
                        </div>
                    </div>
                    <div class="peer bdL p-20 w-30p@lg+ w-100p@lg-">
                        <div class="layers">
                            <div class="layer w-100">
                                <div class="layers">
                                    <div class="layer w-100">
                                        <h5 class="mB-5">Website's seller performance</h5>
                                        <br>
                                        <small class="fw-600 c-grey-700">Ebay</small>
                                        <small class="pull-right c-grey-700 fw-600">Above Standard</small>
                                        <div class="progress mT-10">
                                            <div class="progress-bar bgc-deep-purple-500" role="progressbar"
                                                 aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"
                                                 style="width:100%;"><span class="sr-only">100% Complete</span></div>
                                        </div>
                                        <br>
                                        <small class="fw-600 c-grey-700">Amazon</small>
                                        <small class="pull-right c-grey-700 fw-600">Above Standard</small>
                                        <div class="progress mT-10">
                                            <div class="progress-bar bgc-yellow-700" role="progressbar"
                                                 aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"
                                                 style="width:100%;"></div>
                                        </div>
                                        <br>
                                        <small class="fw-600 c-grey-700">Facebook Marketplace</small>
                                        <small class="pull-right c-grey-700 fw-600">Above Standard</small>
                                        <div class="progress mT-10">
                                            <div class="progress-bar bgc-blue-700" role="progressbar"
                                                 aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"
                                                 style="width:100%;"></div>
                                        </div>
                                        <br>
                                        <small class="fw-600 c-grey-700">Gumtree</small>
                                        <small class="pull-right c-grey-700 fw-600">Above Standard</small>
                                        <div class="progress mT-10">
                                            <div class="progress-bar bgc-light-green-500" role="progressbar"
                                                 aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"
                                                 style="width:100%;"></div>
                                        </div>
                                        <br>
                                        <small class="fw-600 c-grey-700">Google+</small>
                                        <small class="pull-right c-grey-700 fw-600">Above Standard</small>
                                        <div class="progress mT-10">
                                            <div class="progress-bar bgc-red-500" role="progressbar"
                                                 aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"
                                                 style="width:100%;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="masonry-item col-md-6">
            <!-- #Monthly Stats ==================== -->
            <!-- Add a table in the database for stats (sum of current balance or incoming transactions
                 for all the e-commerce platforms) and display em in the graph -->
            <div class="bd bgc-white">
                <div class="layers">
                    <div class="layer w-100 pX-20 pT-20">
                        <h6 class="lh-1">Monthly Stats</h6>
                    </div>
                    <div class="layer w-100 p-20">
                        <canvas id="line-chart" height="220"></canvas>
                    </div>
                    <div class="layer bdT p-20 w-100">
                        <div class="peers ai-c jc-c gapX-20">
                            <div class="peer">
                                <span class="fsz-def fw-600 mR-10 c-grey-800">10% <i
                                            class="fa fa-level-up c-green-500"></i></span>
                                <small class="c-grey-500 fw-600">APPL</small>
                            </div>
                            <div class="peer fw-600">
                                <span class="fsz-def fw-600 mR-10 c-grey-800">2% <i
                                            class="fa fa-level-down c-red-500"></i></span>
                                <small class="c-grey-500 fw-600">Average</small>
                            </div>
                            <div class="peer fw-600">
                                <span class="fsz-def fw-600 mR-10 c-grey-800">15% <i
                                            class="fa fa-level-up c-green-500"></i></span>
                                <small class="c-grey-500 fw-600">Sales</small>
                            </div>
                            <div class="peer fw-600">
                                <span class="fsz-def fw-600 mR-10 c-grey-800">8% <i
                                            class="fa fa-level-down c-red-500"></i></span>
                                <small class="c-grey-500 fw-600">Profit</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="masonry-item col-md-6">
            <!-- Add a database table with the create/delete methods to implement a TODO List
                 so that the users can interact with eachother and save todo lists for tasks tracking -->
            <!-- Todo ==================== -->
            <div class="bd bgc-white p-20">
                <div class="layers">
                    <div class="layer w-100 mB-10">
                        <h6 class="lh-1">Todo List</h6>
                    </div>
                    <div class="layer w-100">
                        <ul class="list-task list-group" data-role="tasklist">
                            <li class="list-group-item bdw-0" data-role="task">
                                <div class="checkbox checkbox-circle checkbox-info peers ai-c">
                                    <input type="checkbox" id="inputCall1" name="inputCheckboxesCall" class="peer">
                                    <label for="inputCall1" class=" peers peer-greed js-sb ai-c">
                                        <span class="peer peer-greed">Call John for Dinner</span>
                                    </label>
                                </div>
                            </li>
                            <li class="list-group-item bdw-0" data-role="task">
                                <div class="checkbox checkbox-circle checkbox-info peers ai-c">
                                    <input type="checkbox" id="inputCall2" name="inputCheckboxesCall" class="peer">
                                    <label for="inputCall2" class=" peers peer-greed js-sb ai-c">
                                        <span class="peer peer-greed">Book Boss Flight</span>
                                        <span class="peer">
                                            <span class="badge badge-pill fl-r badge-success lh-0 p-10">2 Days</span>
                                        </span>
                                    </label>
                                </div>
                            </li>
                            <li class="list-group-item bdw-0" data-role="task">
                                <div class="checkbox checkbox-circle checkbox-info peers ai-c">
                                    <input type="checkbox" id="inputCall3" name="inputCheckboxesCall" class="peer">
                                    <label for="inputCall3" class=" peers peer-greed js-sb ai-c">
                                        <span class="peer peer-greed">Hit the Gym</span>
                                        <span class="peer">
                                            <span class="badge badge-pill fl-r badge-danger lh-0 p-10">3 Minutes</span>
                                        </span>
                                    </label>
                                </div>
                            </li>
                            <li class="list-group-item bdw-0" data-role="task">
                                <div class="checkbox checkbox-circle checkbox-info peers ai-c">
                                    <input type="checkbox" id="inputCall4" name="inputCheckboxesCall" class="peer">
                                    <label for="inputCall4" class=" peers peer-greed js-sb ai-c">
                                        <span class="peer peer-greed">Give Purchase Report</span>
                                        <span class="peer">
                                            <span class="badge badge-pill fl-r badge-warning lh-0 p-10">not important</span>
                                        </span>
                                    </label>
                                </div>
                            </li>
                            <li class="list-group-item bdw-0" data-role="task">
                                <div class="checkbox checkbox-circle checkbox-info peers ai-c">
                                    <input type="checkbox" id="inputCall5" name="inputCheckboxesCall" class="peer">
                                    <label for="inputCall5" class=" peers peer-greed js-sb ai-c">
                                        <span class="peer peer-greed">Watch Game of Thrones Episode</span>
                                        <span class="peer">
                                            <span class="badge badge-pill fl-r badge-info lh-0 p-10">Tomorrow</span>
                                        </span>
                                    </label>
                                </div>
                            </li>
                            <li class="list-group-item bdw-0" data-role="task">
                                <div class="checkbox checkbox-circle checkbox-info peers ai-c">
                                    <input type="checkbox" id="inputCall6" name="inputCheckboxesCall" class="peer">
                                    <label for="inputCall6" class=" peers peer-greed js-sb ai-c">
                                        <span class="peer peer-greed">Give Purchase report</span>
                                        <span class="peer">
                                            <span class="badge badge-pill fl-r badge-success lh-0 p-10">Done</span>
                                        </span>
                                    </label>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection