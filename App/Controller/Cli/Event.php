<?php

class Event extends CliController {
  
  public function finish() {
    foreach (\M\Event::all('status IN (?)', [\M\Event::STATUS_MOVING, \M\Event::STATUS_ERROR]) as $event) {
      if (time() - strtotime($event->lastSignal->createAt) < 60 * 3)
        continue;

      $event->status = \M\Event::STATUS_FINISH;
      $event->save();
    }
  }
}
