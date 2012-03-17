<?php

class NewEventSyntax extends CComponent {

	private $_eventHandlers = array();

	public function onMockEvent($event)
	{
		$this->raiseEvent('onMockEvent',$event);
	}

	public function mockEvent()
	{
		if($this->hasEventHandler('onMockEvent'))
			$this->onMockEvent(new CEvent($this));
	}

	/**
	 * Add an event handler for an event
	 * @param string $eventName the name of the event to bind to
	 * @param Callable $handler the callback to execute when this event is triggered
	 * @return CComponent the current object with the event handler added
	 */
	public function on($eventName, $handler)
	{
		if (!isset($this->_eventHandlers[$eventName]))
			$this->_eventHandlers[$eventName] = new CList();
		$this->_eventHandlers[$eventName][] = $handler;
		return $this; // chainable
	}
	/**
	 * Removes an event handler for an event
	 * @param string $eventName the name of the event from which to unbind the handler(s)
	 * @param Callable|null $handler the handler to unbind, if not specified, all handlers for this event will be unbound
	 * @return CComponent the current object with the event handler(s) removed
	 */
	public function off($eventName, $handler = null)
	{
		if ($handler === null)
			unset($this->_eventHandlers[$eventName]);
		elseif (isset($this->_eventHandlers[$eventName]))
		{
			foreach($this->_eventHandlers[$eventName] as $i => $h) {
				if ($h === $handler) {
					unset($this->_eventHandlers[$eventName][$i]);
				}
			}
		}
		return $this; // chainable
	}
	/**
	 * Triggers an event with the given name
	 * @param string $eventName the name of the event to trigger
	 * @param null $params the parameters to pass to the event handlers
	 * @return boolean whether this event is valid or not
	 */
	public function trigger($eventName, $params = null) {
		if (!isset($this->_eventHandlers[$eventName])) {
			return false;
		}
		$event = new CModelEvent($this,$params);
		foreach($this->_eventHandlers[$eventName] as $handler) {
			call_user_func_array($handler,array($event));
			if ($event->handled) {
				break;
			}
		}
		return $event->isValid;
	}
}

class NewEventSyntaxTest extends CTestCase {
	public function testEvents() {
		$obj = new NewEventSyntax();
		$callback = function($event) {
			// do nothing
		};
		$obj->onMockEvent = $callback; // traditional method
		$obj->on("mockEvent",$callback); // new method
		$iterations = 10000;
		$startTime = microtime(true);
		for($i = 0; $i < $iterations; $i++ ) {
			$obj->mockEvent();
		}
		$endTime = microtime(true);
		echo "Traditional: $iterations iterations in ".($endTime - $startTime)." seconds\n";
		$startTime = microtime(true);
		for($i = 0; $i < $iterations; $i++ ) {
			$obj->trigger("mockEvent");
		}
		$endTime = microtime(true);
		echo "New Syntax: $iterations iterations in ".($endTime - $startTime)." seconds.\n";



	}

	public function testMultipleEvents() {
		$obj = new NewEventSyntax();
		$callback = function($event) {
			// do nothing
		};
		$secondcallback = function($event) {
			// do even more nothing
		};
		$obj->onMockEvent = $callback; // traditional method
		$obj->onMockEvent = $secondcallback;
		$obj->on("mockEvent",$callback); // new method
		$obj->on("mockEvent", $secondcallback);
		$iterations = 10000;
		$startTime = microtime(true);
		for($i = 0; $i < $iterations; $i++ ) {
			$obj->mockEvent();
		}
		$endTime = microtime(true);
		echo "2 Callbacks Traditional: $iterations iterations in ".($endTime - $startTime)." seconds.\n";
		$startTime = microtime(true);
		for($i = 0; $i < $iterations; $i++ ) {
			$obj->trigger("mockEvent");
		}
		$endTime = microtime(true);
		echo "2 Callbacks New Syntax: $iterations iterations in ".($endTime - $startTime)." seconds.\n";



	}
}