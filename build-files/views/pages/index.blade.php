<h1>hello this is capro/build.</h1>
config() test: {{ config('app.test') }}<br>
config() unknown: {{ config('app.unknown', 'default') }}<br>
Config::get() test: {{ Config::get('app.test') }}<br>
Config::get() unknown: {{ Config::get('app.unknown', 'default') }}<br>
Pages count: {{ Capro::pages()->count() }}<br>

