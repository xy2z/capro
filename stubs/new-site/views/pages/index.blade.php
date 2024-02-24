<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>{{ config('app.title') }}</title>

		<style>
			html,
			body {
				height: 100%;
				margin: 0;
				padding: 0;
			}

			* {
				box-sizing: border-box;
				max-width: 100%;
			}

			body {
				height: 65%;
				font-family: 'Open Sans', Tahoma, Geneva, Verdana, sans-serif;
				font-size: 17px;
				background: #f9f9f9;
				background: #2C2C2C;
				background: #1e2932;
				color: #CD9;
				padding: 0 30px;
				display: flex;
				align-items: center;
			}

			.app {
				width: 100%;
				text-align: center;
			}

			h1 {
				display: inline-block;
				margin-bottom: 2em;
				border-bottom: 4px solid #b56f33;
				border-radius: 4px;
				padding-bottom: 0.1em;
			}

			nav a {
				opacity: 0.6;
				color: #5cf;
				text-decoration: none;
				margin: 0 10px;
				border-bottom: 1px dotted rgba(255, 255, 255, 0.4);
			}

			nav a:hover {
				opacity: 1;
			}
		</style>
	</head>

	<body>
		<div class="app">
			<h1>{{ config('app.headline') }}</h1>

			<nav>
				@foreach (config('app.links') as $title => $url)
					<a target="_blank" href="{{ $url }}">{{ $title }}</a>
				@endforeach
			</nav>
		</div>
	</body>

</html>
