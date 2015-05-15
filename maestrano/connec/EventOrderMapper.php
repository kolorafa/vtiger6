<?php

/**
* Map Connec EventOrder representation to a vTiger Contact-Ticket relationship
*/
class EventOrderMapper extends BaseMapper {
  public function __construct() {
    parent::__construct();

    $this->connec_entity_name = 'EventOrder';
    $this->local_entity_name = 'EventOrder';
    $this->connec_resource_name = 'event_orders';
    $this->connec_resource_endpoint = 'event_orders';
  }

  // No vTiger model
  protected function getId($event_order) {
    return null;
  }

  // Return a standard object
  protected function loadModelById($local_id) {
    return (object) array();
  }

  // Return a standard object
  public function matchLocalModel($organization_hash) {
    return (object) array();
  }

  // Map the Connec resource attributes onto the vTiger Contact/Ticket
  protected function mapConnecResourceToModel($event_order_hash, $event_order) {
    // Retrieve the Event
    if($this->is_set($event_order_hash['event_id'])) {
      $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($event_order_hash['event_id'], 'EVENT');
      $eventMapper = new EventMapper();
      $vtiger_event = $eventMapper->loadModelById($mno_id_map['app_entity_id']);
    }

    // Map the list of attendees
    foreach ($event_order_hash['attendees'] as $attendee_hash) {
      // Contact attending the event
      $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($attendee_hash['person_id'], 'PERSON');
      $contactMapper = new ContactMapper();
      $vtiger_contact = $contactMapper->loadModelById($mno_id_map['app_entity_id']);

      // Ticket type
      $mno_id_map = MnoIdMap::findMnoIdMapByMnoIdAndEntityName($attendee_hash['event_ticket_id'], 'TICKET');
      $ticketMapper = new TicketMapper();
      $vtiger_ticket = $ticketMapper->loadModelById($mno_id_map['app_entity_id']);

      // Link Event / Ticket / Contact
      $vtiger_contact->save_related_module('EventManagement', $vtiger_event->id, 'Contacts', $vtiger_contact->id);
      $vtiger_contact->save_related_module('EventTicket', $vtiger_ticket->id, 'Contacts', $vtiger_contact->id);
      $vtiger_event->save_related_module('Contacts', $vtiger_contact->id, 'EventManagement', $vtiger_event->id);
      $vtiger_event->save_related_module('Contacts', $vtiger_contact->id, 'EventTicket', $vtiger_ticket->id);
    }
  }

  // Do not push
  protected function mapModelToConnecResource($event_order) {
    return $array();
  }

  // Persisted when mapping
  protected function persistLocalModel($event_order, $event_order_hash) {

  }
}