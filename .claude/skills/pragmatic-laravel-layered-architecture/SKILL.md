---
name: pragmatic-laravel-layered-architecture
description: "Use this skill for Laravel backend tasks where implementing, changing, or reviewing functionality requires architecture decisions. This skill defines the project's custom architectural approach and should be treated as the source of truth for architecture, layer placement, and responsibility boundaries. Use it when deciding where code belongs, how to split responsibilities, how to wire a use case end-to-end, or whether something should be a Controller, Form Request, MCP Tool, Action, Core class, Value Object, Data object, Enum, Model, Event, Listener, Job, Contract, Integration, or Provider."
metadata:
  author: yuriymarad
---

# Pragmatic Laravel Layered Architecture

A practical backend architecture for Laravel projects that keeps code split by clear responsibilities, helping the codebase stay maintainable as it grows and easier to test.

This approach does not try to force pure Clean Architecture, strict DDD, or other abstractions into Laravel. It simply extends the standard way of building Laravel applications by adding a few practical architectural components and rules that make layer boundaries and responsibilities clearer.

## Architecture Overview

This section describes the main components of our application architecture:

- Http / Console / MCP
- Actions
- Core
- Contracts
- Integrations
- Models
- Values
- Data
- Enums
- Events / Listeners
- Jobs
- Casts
- Providers
- Exceptions

**1. Http / Console / MCP**

Question: How does the outside world enter the system?

Responsibility:
Entry points of the application: HTTP controllers, API, form requests, console commands, MCP tools.
They receive input, validate or normalize request data, call an Action, and return output.

Rules:
- Keep this layer thin.
- Do not put business logic here.
- Use separate Request classes to validate incoming data.
- Do not orchestrate complex workflows here.

Path: `app/Http/Controllers/`, `app/Http/Requests/`, `app/Console/Commands/`, `app/Mcp/`

Example:
```php
class PlaceOrderController
{
    public function store(PlaceOrderRequest $request, PlaceOrderAction $action): JsonResponse
    {
        $order = $action->handle(PlaceOrderData::fromRequest($request));

        return response()->json(['id' => $order->id]);
    }
}
```

**2. Actions**

Question: What should the system do in this use case?

Responsibility:
Application/use-case layer.
Actions coordinate one clear business scenario, define the order of steps, call Core components, use Models when needed, and dispatch Events or Jobs when needed.
An Action does not have to be a dumb proxy. It can contain use-case-specific logic. But when some piece of logic becomes important, reusable, or complex, it should move to Core.

Rules:
- One Action = one use case.
- Name Actions by intent: `CreateOrderInvoiceAction` etc.
- Keep orchestration here, not reusable domain mechanics

Path: `app/Actions/{Domain}/`

Example:
```php
class PlaceOrderAction
{
    public function __construct(
        private PaymentGateway $payments,
        private CartTotalCalculator $totals,
    ) {}

    public function handle(PlaceOrderData $data): Order
    {
        ...
        $total = $this->totals->calculate($data->items);
        $order = Order::create([...]);
        OrderPlaced::dispatch($order);

        return $order;
    }
}
```

**3. Core**

Question: How does the internal product logic work?

Reusable internal product mechanics: business rules, calculations, evaluators, parsers, builders, selectors, decision logic, and domain-specific algorithms.

Folder naming:
Core folders should be named by product area or internal mechanism, not by generic technical type.

Good:
Core/AlertRules
Core/MarketData
Core/PriceDigests

Avoid:
Core/Services
Core/Helpers
Core/Processors

Rules:
- Core class should explain a reusable product mechanism, not a specific use case
- if the class answers “what should the system do now?”, it is probably an Action
- if it answers “how does this product mechanism work?”, it belongs to Core
- Core should not know about HTTP requests, console commands, MCP tools, or response formats
- Core should not depend on vendor-specific integrations directly; use Contracts when external capabilities are needed

Path: `app/Core/{ProductArea}/`

Example:
```php
// app/Core/Pricing/CartTotalCalculator.php
class CartTotalCalculator
{
    public function calculate(array $items, ?Discount $discount = null): Money
    {
        $subtotal = array_reduce(
            $items,
            fn (Money $sum, CartItem $item) => $sum->add($item->lineTotal()),
            Money::zero('USD'),
        );

        return $discount?->applyTo($subtotal) ?? $subtotal;
    }
}
```

**4. Contracts**

Question: What does the system need to be able to do, without knowing who does it?

Responsibility:
Interfaces for capabilities that may have different implementations.
Contracts describe what the application needs, not how it is implemented.
Use Contracts when the implementation is external, technical, or replaceable.

Path: `app/Contracts/`

Example:
```php
interface PaymentGateway
{
    public function charge(Money $amount, string $token): ChargeResult;
}
```

**5. Integrations**

Question: How do we talk to external systems or technical infrastructure?

Responsibility:
Concrete implementations of Contracts and adapters to external APIs, SDKs, services, or infrastructure.

Rules:
- Keep vendor-specific code here
- Implement Contracts, do not define business rules
- Core should depend on Contracts, not concrete Integrations

Path: `app/Integrations/{Provider}/`

Example:

```php
final class StripePaymentGateway implements PaymentGateway
{
    public function charge(Money $amount, string $token): ChargeResult
    {
        // Stripe-specific code here
    }
}
```

**6. Models**

Question: What persisted entities exist and how are they stored?

Responsibility:
Eloquent persistence models.
Models describe database mapping, relationships, casts, scopes, accessors/mutators, and simple state-related behavior.

Rules:
- Models should be responsible only for persisted state and state-related behavior
- Do not put business logic, use-case orchestration, or product mechanics in Models

Path: `app/Models/`

Example:
```php
class Order extends Model
{
    protected $casts = [
        'status' => OrderStatus::class,
        'total'  => MoneyCast::class,
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
```

**7. Values**

Question: Is this primitive just a technical value, or does it represent a meaningful domain concept?

Responsibility:
Value Objects for meaningful domain values that may contain validation, invariants, normalization, or behavior.

Rules:
- use Values to avoid primitive obsession and keep domain meaning explicit

Path: `app/Values/`

Example:
```php
final class Money
{
    public function __construct(
        public readonly int $cents,
        public readonly string $currency,
    ) {}

    public function add(Money $other): self
    {
        return new self($this->cents + $other->cents, $this->currency);
    }
}
```

**8. Data**

Question: What data shape is passed between parts of the system?

Responsibility:
DTOs/data carriers.
Data objects make method signatures explicit and avoid raw arrays when the shape is important.

Rules:
- Value Object = meaningful domain value with behavior/invariants
- Data Object = structured data container

Path: `app/Data/`

Example:
```php
final class PlaceOrderData
{
    public function __construct(
        public int $customerId,
        public array $items,
        public string $paymentToken,
    ) {}
}
```

**9. Enums**

Question: What fixed set of states or types exists in the system?

Responsibility:
Fixed allowed values and states.
Enums replace magic strings and centralize allowed values.

Path: `app/Enums/`

Example:
```php
enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid    = 'paid';
    case Shipped = 'shipped';
}
```

**10. Events / Listeners**

Question: What happened, and what should react to it?

Responsibility:
Use Events when a meaningful system fact happens and the next reactions should be decoupled from the main use case. They make side effects, notifications, broadcasting, and async workflows easier to separate and trace.

Rules:
- Keep Events small
- Events should describe what happened
- Do not put business logic in Events
- Keep Listeners thin
- Do not hide large business workflows inside Listeners

Path: `app/Events/`, `app/Listeners/`

Example:
```php
// app/Events/OrderPlaced.php
class OrderPlaced
{
    public function __construct(public Order $order) {}
}

// app/Listeners/SendOrderConfirmation.php
class SendOrderConfirmation
{
    public function __construct(private Mailer $mailer) {}

    public function handle(OrderPlaced $event): void
    {
        $this->mailer->send($event->order);
    }
}
```

**11. Jobs**

Question: What work needs controlled background execution?

Jobs define how work is executed outside the main flow.
They are used for slow, retryable, delayed, chunked, or external-service-related work. A Job should usually call an Action or Core component instead of owning the main business logic itself.

Rules:
- Jobs describe execution, not business responsibility
- Use Jobs for background work, retries, delays, chunks, and batches
- Do not use Jobs as primary business services
- Keep business flow in Actions
- Keep reusable product logic in Core

Path: `app/Jobs/`

Example:
```php
class GenerateInvoicePdfJob implements ShouldQueue
{
    public function __construct(public int $orderId) {}

    public function handle(GenerateInvoicePdfAction $action): void
    {
        $action->handle($this->orderId);
    }
}
```

**12. Casts**

Question: How is a database value converted into an object and back?

Responsibility:
Bridge between Eloquent persistence and domain-friendly objects.
Use Casts to convert DB/JSON values to Value Objects and back.

Path: `app/Casts/`

Example:
```php
class MoneyCast implements CastsAttributes
{
    public function get($model, $key, $value, $attributes): Money
    {
        $data = json_decode($value, true);

        return new Money($data['cents'], $data['currency']);
    }

    public function set($model, $key, $value, $attributes): string
    {
        return json_encode(['cents' => $value->cents, 'currency' => $value->currency]);
    }
}
```

**13. Providers**

Question: How is the application wired together?

Responsibility:
Laravel framework wiring.
Providers bind interfaces to implementations, configure services, and register framework-specific behavior.

Path: `app/Providers/`

Example:
```php
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, StripeGateway::class);
    }
}
```

**14. Exceptions**

Use custom Exceptions when a failure has product meaning or needs to be handled differently.

Path: `app/Exceptions/`

Example:
```php
final class OrderAlreadyPaidException extends DomainException
{
    public static function for(Order $order): self
    {
        return new self("Order {$order->id} is already paid.");
    }
}
```
