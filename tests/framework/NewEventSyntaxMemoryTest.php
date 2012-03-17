<?php

class NewEventSyntaxMemory extends CComponent {

	private $_eventHandlers = array();

	public function onMockEvent($event)
	{
		$this->raiseEvent('onMockEvent',$event);
	}

	public function mockEvent()
	{
		if($this->hasEventHandler('onMockEvent'))
			$this->onMockEvent(new CModelEvent($this));
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

class NewEventSyntaxMemoryTest extends CTestCase {
	public function testEvents() {
		$obj = new NewEventSyntaxMemory();
		$callback = function($event) {
			// do nothing
		};
		$obj->on("mockEvent",$callback); // new method
		$iterations = 10000;
		$startTime = microtime(true);
		for($i = 0; $i < $iterations; $i++ ) {
			$obj->trigger("mockEvent");
		}
		$endTime = microtime(true);
		echo "New Syntax: $iterations iterations in ".($endTime - $startTime)." seconds (".memory_get_peak_usage()." bytes)\n";



	}
}