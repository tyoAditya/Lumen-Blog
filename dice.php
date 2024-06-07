<?php
use OpenTelemetry\API\Globals;


class Dice {

    private $tracer;

    function __construct() {
        $tracerProvider = Globals::tracerProvider();
        // $this->tracer = $tracerProvider->getTracer(
        //   'dice-lib',
        //   '0.1.0',
        //   'https://opentelemetry.io/schemas/1.24.0'
        // );
        $this->tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');
    }

    public function roll($rolls) {
        $span = $this->tracer->spanBuilder("rollTheDice")->startSpan();
        $result = [];
        for ($i = 0; $i < $rolls; $i++) {
            $result[] = $this->rollOnce();
        }

        // END SPAN
        $span->end();

        return $result;
    }

    private function rollOnce() {
        // NESTED SPAN
        $parent = OpenTelemetry\API\Trace\Span::getCurrent();
        $scope = $parent->activate();
        try {
            $span = $this->tracer->spanBuilder("rollTheDice")->startSpan();
            $result = random_int(1, 6);
            $span->end();
        } finally {
            $scope->detach();
        }
        return $result;
    }
}
