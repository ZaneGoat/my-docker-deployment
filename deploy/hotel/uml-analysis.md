# UML Analysis - Hotel Management System

This document summarizes the main UML analysis diagrams for the current Flask hotel management project.

## 1. Use Case Diagram

```mermaid
flowchart LR
    Admin([Admin])
    Receptionist([Receptionist])

    Login((Login))
    Dashboard((View Dashboard))
    Rooms((Manage Rooms))
    Clients((Manage Clients))
    Reservations((Manage Reservations))
    CheckIn((Process Check-In))
    CheckOut((Process Check-Out))
    Services((Manage Services))
    Payments((Manage Payments))
    Invoice((Export Invoice PDF))
    Receipt((Print / Export Receipt))
    Cancel((Cancel Reservation))
    Delete((Delete Reservation / Payment))

    Admin --> Login
    Receptionist --> Login

    Admin --> Dashboard
    Receptionist --> Dashboard

    Admin --> Rooms
    Receptionist --> Clients
    Receptionist --> Reservations
    Receptionist --> CheckIn
    Receptionist --> CheckOut
    Receptionist --> Services
    Receptionist --> Payments
    Receptionist --> Invoice
    Receptionist --> Receipt

    Admin --> Services
    Admin --> Rooms
    Admin --> Delete
    Admin --> Cancel

    Reservations --> Invoice
    Payments --> Receipt
    Reservations --> Cancel
    Reservations --> Delete
```

## 2. Class Diagram

```mermaid
classDiagram
    direction LR

    class User {
        +int id
        +string username
        +string password_hash
        +string role
    }

    class Room {
        +int id
        +string number
        +string type
        +float price_per_night
        +int floor
        +string status
    }

    class Client {
        +int id
        +string cin
        +string first_name
        +string last_name
        +string phone
        +string email
        +string address
    }

    class Reservation {
        +int id
        +int client_id
        +int room_id
        +date arrival_date
        +date departure_date
        +datetime actual_check_in
        +datetime actual_check_out
        +float extra_charges
        +float total_amount
        +string status
        +datetime created_at
    }

    class Service {
        +int id
        +string name
        +float price
    }

    class ReservationService {
        +int id
        +int reservation_id
        +int service_id
        +int quantity
        +datetime date_consumed
    }

    class Payment {
        +int id
        +int reservation_id
        +float amount
        +string method
        +datetime timestamp
    }

    Client "1" --> "0..*" Reservation : books
    Room "1" --> "0..*" Reservation : assigned to
    Reservation "1" --> "0..*" ReservationService : consumes
    Service "1" --> "0..*" ReservationService : referenced by
    Reservation "1" --> "0..*" Payment : paid by
```

## 3. Reservation State Diagram

```mermaid
stateDiagram-v2
    [*] --> Confirmee
    Confirmee --> CheckedIn : check-in
    Confirmee --> Annulee : cancel
    CheckedIn --> CheckedOut : check-out
    CheckedIn --> Annulee : cancel before close
    CheckedOut --> [*]
    Annulee --> [*]
```

## 4. Sequence Diagram - Reservation Lifecycle

```mermaid
sequenceDiagram
    actor Receptionist
    participant UI as Web UI
    participant App as Flask App
    participant DB as SQLite DB

    Receptionist->>UI: Create reservation form
    UI->>App: POST /reservations/add
    App->>DB: Check client, room, and overlap
    DB-->>App: Validation result
    App->>DB: Insert Reservation
    DB-->>App: Reservation saved
    App-->>UI: Flash confirmation

    Receptionist->>UI: Process check-in
    UI->>App: POST /check-in/{res_id}
    App->>DB: Verify reservation status and room availability
    DB-->>App: Validation result
    App->>DB: Update Reservation.status = Checked-In
    App->>DB: Update Room.status = Occupé
    DB-->>App: Commit
    App-->>UI: Flash success

    Receptionist->>UI: Register payment
    UI->>App: POST /reservations/pay/{res_id}
    App->>DB: Insert Payment
    DB-->>App: Commit
    App-->>UI: Flash payment confirmation

    Receptionist->>UI: Process check-out
    UI->>App: POST /check-out/{res_id}
    App->>DB: Update Reservation.status = Checked-Out
    App->>DB: Update Room.status = Disponible
    DB-->>App: Commit
    App-->>UI: Flash checkout success
    UI->>App: GET /reservations/invoice/pdf/{res_id}
    App-->>UI: PDF invoice download
```

## 5. Main Business Rules

- A reservation must not overlap another active reservation for the same room.
- Check-in is allowed only on or after the arrival date and before the departure date.
- Check-out closes the stay and makes the room available again.
- Services can be attached to an active reservation and increase the total amount.
- Payments are tied to a reservation and are used to track balance.
- Closed reservations are intended to be read-only except for export actions.

## 6. Notes For Report

- The system is centered on `Reservation` as the main aggregate.
- `ReservationService` acts as the association class between `Reservation` and `Service`.
- `Payment` and `Service` are both dependent on an existing reservation context.
- The current implementation already maps cleanly to a standard hotel PMS domain model.
