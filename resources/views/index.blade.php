<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="{{ app()->getLocale() }}"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang="{{ app()->getLocale() }}"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang="{{ app()->getLocale() }}"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="{{ app()->getLocale() }}"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Factom is Cool!</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="css/app.css">
    <style>
        body {
            padding-top: 50px;
            padding-bottom: 20px;
        }
    </style>

    <script type="text/javascript">
        var commandsJson = '{!! $commandsJson !!}';
        var resultsJson = '{!! $resultsJson !!}';
        var lastCommand = '{!! $lastCommand !!}';
    </script>
</head>
<body>
<!--[if lt IE 8]>
<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
<![endif]-->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container header">
        <div class="navbar-header">
            <a class="navbar-brand" href="#">{{ $siteName }}</a>
        </div>
        <div class="github-count">
            <iframe src="https://ghbtns.com/github-btn.html?user=1000nettles&repo=factom-api-php&type=star&count=true" frameborder="0" scrolling="0" width="170px" height="20px"></iframe>
        </div>
    </div>
</nav>

@if(Session::has('error'))
    <div class="alert alert-danger">
        {{ Session::get('error') }}
    </div>
@endif

<!-- Main jumbotron for a primary marketing message or call to action -->
<div class="jumbotron">
    <div class="container commands-app" id="commands-app">
        <form class="form-horizontal" role="form" method="POST" action="{{ $apiUrl }}">
            {{ csrf_field() }}
            <div class="row">
                <div v-bind:class="{ 'col-md-6': hasCommandParams, 'col-md-12': !hasCommandParams }">
                    <div class="wrapper">
                        <h3>Factomd Command</h3>
                        <div class="form-group">
                            <select id="command-picker" name="command" v-model="selectedCommand">
                                <option v-for="command in commands" v-bind:value="command.id">
                                    @{{ command.identifier }}
                                </option>
                            </select>
                        </div>
                        <small>For more information, please see <a target="_blank" v-bind:href="getDocumentationUrl">the API documentation.</a></small>
                    </div>
                </div>
                <div v-bind:class="{ 'col-md-6': hasCommandParams, 'col-md-12': !hasCommandParams }">
                    <div id="command-params" v-if="hasCommandParams">
                        <div class="wrapper">
                            <h3>Parameters</h3>
                            <div class="form-group">
                                <ul>
                                    <li v-for="(command_param, index) in currentCommandObj.commands_params">
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-addon" :id="command_param.id">@{{ command_param.identifier }}</span>
                                            <input type="text" class="form-control" :name="command_param.identifier" :aria-describedby="command_param.id">
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 submit-container">
                    <div class="form-group">
                        <button type="submit" class="btn btn-success">Submit API Call</button>
                    </div>
                </div>
                <div class="col-md-12 data-container" v-if="hasResultsJson">
                    <pre class="result">@{{ getResultsJson }}</pre>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="container bottom">
    <!-- Example row of columns -->
    <div class="row">
        <div class="col-md-12">
            <h2>What Is This?</h2>
            <p><em>Factom is Cool!</em> is a service making it easy to call the Factom API. No running of local nodes, no configuration, just run the command you want above. Please see the <a href="https://docs.factom.com/api">API documentation</a> for more info.</p>
            <h2>Is My Request Secure?</h2>
            <p>We do not log any requests or parameters submitted to our node, and we are connecting via a TLS certificate and username / password authentication. That said, if you're concerned about privacy please run your own node <em>or</em> your own instance of this site: [insert GitHub URL here]</p>
            <h2>Why Can't I Run <em>factom-walletd</em> Commands Here?</h2>
            <p>I don't want people importing private keys or generating addresses and asking me for them. If you need to generate a public / private key pair, please use the <a href="https://www.factom.com/devs/docs/howto/use-factoidpapermill">FactoidPapermill</a>.</p>
        </div>
    </div>

    <hr>

    <footer>
        <div class="donation">
            <small><strong>Factom / Factoid Donation Address:</strong><br>{{ $donationAddr }}</small>
        </div>
        <div class="factomd-status">
            <div class="circle {{ strtolower($status) }}"></div>
            <small><strong>Factomd Node Status: {{ $status }}</strong></small>
            <br>
            <small><strong>v0.1.0</strong></small>
        </div>
    </footer>
</div> <!-- /container -->

<template id="command-params-template">
    <ul>
        <li v-for="command in commands">
            @{{ command }} - @{{ time }}
        </li>
    </ul>
</template>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="js/vendor/jquery-1.11.2.min.js"><\/script>')</script>

<script src="js/vendor/bootstrap.min.js"></script>
<script src="js/vendor/modernizr-2.8.3-respond-1.4.2.min.js"></script>
<script src="js/app.js"></script>
</body>
</html>