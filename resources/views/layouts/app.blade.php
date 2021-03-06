<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	{{-- CSRF Token --}}
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>BDE-UTC - Portail des Associations</title>

	{{-- Custom Bootstrap 4 --}}
	<link rel="stylesheet" href="{{ asset('css/bootstrap.css') }}">

	{{-- Font Awesome 5 --}}
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">

	{{-- Custom Styles --}}
	<link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
	<nav class="navbar navbar-expand-md navbar-dark fixed-top">
		<a class="navbar-brand" href="{{ url('/') }}">Portail des associations</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>

		<div class="collapse navbar-collapse" id="navbarSupportedContent">
			<!-- Right Side Of Navbar -->
			<ul class="navbar-nav ml-auto">

				@auth
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							{{ Auth::user()->name }} <span class="caret"></span>
						</a>
						<div class="dropdown-menu" aria-labelledby="navbarDropdown">
							<a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
								Se déconnecter
							</a>

							<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
								@csrf
							</form>
						</div>
					</li>
				@else
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							Se connecter <span class="caret"></span>
						</a>
						<div class="dropdown-menu" aria-labelledby="navbarDropdown">
							@foreach (config('auth.services') as $name => $provider)
								@if (!$provider['loggable'])
									@continue
								@endif
								<a class="dropdown-item" href="{{ route('login.show', ['provider' => $name, 'redirect' => $redirect ?? url()->previous()]) }}">
									{{ $provider['name'] }}
								</a>
							@endforeach
								<a class="dropdown-item" href="{{ route('login', ['see' => 'all', 'redirect' => $redirect ?? url()->previous()]) }}">
									Tout voir
								</a>
						</div>
					</li>
				@endauth

			</ul>
		</div>
	</nav>

	<div class="container py-4 py-md-5" style="height: 100%">
		@yield('content')
	</div>

	<!-- Bootstrap Scripts -->
	<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>

	<script type="text/javascript">
		$(function () {
			$('[data-toggle="tooltip"]').tooltip()
		});
	</script>

	@yield('script')
</body>
</html>
