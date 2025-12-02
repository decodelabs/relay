# Relay — Package Specification

> **Cluster:** `integration`
> **Language:** `php`
> **Milestone:** `m4`
> **Repo:** `https://github.com/decodelabs/relay`
> **Role:** Email

## Overview

### Purpose

Relay provides accessible tools for creating and sending emails. The package focuses on email address handling and HTML email generation, offering a simple API for working with mailboxes and generating email content.

Currently, Relay is in early development and primarily provides mailbox management and a basic email HTML generator ported from Tagged. The functionality is expected to evolve in future versions.

### Non-Goals

- Relay does not provide SMTP client functionality or email sending capabilities.
- It does not handle email attachments or multipart messages.
- It does not provide email queue management or batch sending.
- It does not implement email templates or template engines.
- It does not handle email authentication or security features beyond basic validation.

## Role in the Ecosystem

### Cluster & Positioning

Relay belongs to the **integration** cluster, focusing on external service integration capabilities. It complements other integration packages like `telegraph` (mailing list manager) and `hydro` (HTTP client) by providing foundational email handling tools.

### Usage Contexts

- **Email address validation**: Validating and normalizing email addresses
- **Mailbox management**: Working with collections of email addresses
- **HTML email generation**: Creating HTML email content with responsive styling
- **Email formatting**: Formatting email addresses for display or transmission
- **Integration preparation**: Preparing email data for use with other email services or libraries

## Public Surface

### Key Types

- **`Mailbox`** (class): Represents a single email address with an optional display name. Handles parsing, validation, and formatting of email addresses. Implements `Stringable` and `Dumpable` interfaces.

- **`MailboxList`** (class): A collection of `Mailbox` instances, providing list management capabilities. Implements `IteratorAggregate`, `Countable`, `Stringable`, and `Dumpable` interfaces. Supports parsing comma-separated email address strings.

- **`Mail\Generator`** (class): HTML email generator that creates responsive email HTML with built-in styling. Requires Tagged library for operation. Provides methods for creating email document structure, content areas, and common email elements.

### Main Entry Points

**Mailbox:**
- `Mailbox::create(string|Mailbox|null $mailbox, ?string $name = null): ?Mailbox` — Factory method to create a mailbox instance
- `new Mailbox(string $address, ?string $name = null)` — Constructor
- `$mailbox->address` — Email address property (validated on assignment)
- `$mailbox->name` — Optional display name property
- `$mailbox->domain` — Read-only property returning the domain portion of the address
- `(string)$mailbox` — String conversion returns formatted "Name <address>" or just "address"

**MailboxList:**
- `MailboxList::parse(?string $mailboxes): MailboxList` — Parse comma-separated email addresses into a list
- `new MailboxList(string|Mailbox ...$mailboxes)` — Constructor
- `$list->add(string|Mailbox|MailboxList ...$mailboxes): void` — Add mailboxes to the list
- `$list->remove(string|Mailbox|MailboxList ...$mailboxes): void` — Remove mailboxes from the list
- `$list->get(string $address): ?Mailbox` — Get a mailbox by address
- `$list->getFirst(): ?Mailbox` — Get the first mailbox in the list
- `$list->has(string|Mailbox $address): bool` — Check if a mailbox exists
- `$list->isEmpty(): bool` — Check if the list is empty
- `$list->clear(): void` — Clear all mailboxes
- `$list->toNameList(): array<string,string>` — Get array of address => name mappings
- `$list->toArray(): array<string,Mailbox>` — Get all mailboxes as an array
- `(string)$list` — String conversion returns comma-separated formatted addresses

**Mail\Generator:**
- `new Mail\Generator()` — Constructor (requires Tagged)
- `$generator->document(string $subject, mixed $content, array $bodyAttributes = []): Markup` — Generate complete HTML email document
- `$generator->body(mixed $content, ?array $tagStyles = null, array $attributes = []): Element` — Generate email body
- `$generator->contentArea(mixed $content, ?array $tagStyles = null, array $attributes = []): Element` — Generate content area
- `$generator->section(mixed $content, ?array $tagStyles = null, array $attributes = []): Element` — Generate section
- `$generator->h1()` through `$generator->h6()` — Generate heading elements
- `$generator->p(mixed $content, ?array $tagStyles = null, array $attributes = []): Element` — Generate paragraph
- `$generator->link(string $url, mixed $content, ?array $tagStyles = null, array $attributes = []): Element` — Generate link
- `$generator->image(string $url, int $width, int $height, ?string $alt = null, ?array $tagStyles = null, array $attributes = []): Element` — Generate image
- `$generator->banner(string $url, int $width, int $height, ?string $alt = null): Element` — Generate banner image
- `$generator->card(mixed $content, ?array $tagStyles = null, array $attributes = []): Element` — Generate card
- `$generator->columns(mixed ...$contents): Element` — Generate column layout
- `$generator->rows(mixed ...$contents): Element` — Generate row layout
- `$generator->gutter(string $width, mixed $content, ?array $tagStyles = null, array $attributes = []): Element` — Generate content with gutters
- `$generator->footer(mixed $content, ?array $tagStyles = null, array $attributes = []): Element` — Generate footer
- `$generator->smallprint(mixed $content, ?array $tagStyles = null, array $attributes = []): Element` — Generate small print section
- `$generator->previewText(?string $content): Element` — Generate preview text
- `$generator->container(mixed $content, ?array $tagStyles = null, array $attributes = []): Element` — Generate container
- `$generator->css(): Element` — Generate CSS stylesheet element

## Dependencies

### Decode Labs

- **`decodelabs/exceptional`**: Used for exception handling throughout the package.

### External

- **PHP**: See `composer.json` for supported PHP versions.

### Optional

- **`decodelabs/tagged`**: Required for `Mail\Generator` functionality. Detected at runtime, throws `ComponentUnavailable` exception if not available. The generator cannot function without Tagged.
- **`decodelabs/nuance`**: Detected at runtime if installed, used for `Dumpable` interface support on `Mailbox` and `MailboxList` classes.

## Behaviour & Contracts

### Invariants

- A `Mailbox` instance always contains a valid email address (validated on construction and assignment).
- Email addresses are normalized to lowercase.
- The `address` property setter validates email format using PHP's `filter_var()`.
- `MailboxList` stores mailboxes indexed by their address, preventing duplicates.
- `Mail\Generator` requires Tagged to be available at construction time.

### Input & Output Contracts

**Mailbox:**
- Constructor accepts `string $address` and optional `?string $name`.
- Address can be in formats: `"address@domain.com"` or `"Name <address@domain.com>"`.
- Address setter automatically parses name from formatted strings.
- Address setter normalizes " at " and " dot " to "@" and ".".
- Invalid addresses throw `InvalidArgument` exceptions.
- String conversion returns RFC 5322 compliant format: `"Name <address>"` or `"address"`.

**MailboxList:**
- `parse()` handles comma-separated email addresses.
- `parse()` handles addresses that span multiple lines (comma continuation).
- `add()` accepts strings, `Mailbox` instances, or other `MailboxList` instances.
- Duplicate addresses (by email address) are automatically deduplicated.
- String conversion returns comma-separated formatted addresses.

**Mail\Generator:**
- All methods return Tagged `Element` or `Markup` objects.
- Styles are applied from predefined style sheets (`Styles` and `MobileStyles` constants).
- Mobile-responsive styles are automatically included via media queries.
- Content width defaults to 600px but can be customized via styles.

## Error Handling

- **Invalid email address**: `Mailbox` constructor and address setter throw `InvalidArgument` exceptions for invalid email addresses.
- **Tagged unavailable**: `Mail\Generator` constructor throws `ComponentUnavailable` exception if Tagged is not installed.
- **Null mailbox**: `Mailbox::create()` returns `null` for null input (does not throw).
- **Empty list operations**: `MailboxList::getFirst()` returns `null` for empty lists.

## Configuration & Extensibility

### Customizing Mail\Generator Styles

The `Mail\Generator` class defines styles in two class constants:
- `Styles`: Base styles for email elements
- `MobileStyles`: Responsive styles for mobile devices

These can be extended by creating a subclass and overriding the constants or the `getStylesFor()` method.

### Creating Custom Email Components

The generator provides a `container()` method that can be used as a base for custom components. Custom components should follow the same pattern:
- Accept content, optional tag styles, and attributes
- Use `getStylesFor()` to retrieve relevant styles
- Return Tagged `Element` instances

## Interactions with Other Packages

- **Exceptional**: Used for all exception handling.
- **Tagged**: Required dependency for `Mail\Generator`. The generator cannot function without it.
- **Nuance**: Optional dependency for `Dumpable` interface support, enabling debugging and inspection capabilities.
- **Elementary**: Used internally by `Mail\Generator` for style management (`StyleSheet` and `StyleList`).
- **Telegraph**: Relay's mailbox types may be used with Telegraph for mailing list management.

## Usage Examples

### Basic Mailbox Usage

```php
use DecodeLabs\Relay\Mailbox;

// Create a mailbox
$mailbox = new Mailbox('test@example.com', 'Test User');
echo $mailbox; // "Test User" <test@example.com>

// Access properties
echo $mailbox->address; // test@example.com
echo $mailbox->name; // Test User
echo $mailbox->domain; // example.com

// Parse formatted address
$mailbox = new Mailbox('Test User <test@example.com>');
// Automatically extracts name and address

// Factory method
$mailbox = Mailbox::create('test@example.com', 'Test User');
```

### MailboxList Usage

```php
use DecodeLabs\Relay\MailboxList;

// Create from individual addresses
$list = new MailboxList('user1@example.com', 'user2@example.com');

// Parse comma-separated string
$list = MailboxList::parse('User One <user1@example.com>, user2@example.com');

// Add mailboxes
$list->add('user3@example.com', 'User Three <user4@example.com>');

// Check and retrieve
if ($list->has('user1@example.com')) {
    $mailbox = $list->get('user1@example.com');
}

// Iterate
foreach ($list as $address => $mailbox) {
    echo "$address: {$mailbox->name}\n";
}

// Convert to string
echo $list; // "User One" <user1@example.com>, user2@example.com, ...
```

### Email Generation

```php
use DecodeLabs\Relay\Mail\Generator;

// Create generator (requires Tagged)
$generator = new Generator();

// Generate complete email document
$html = $generator->document(
    'Welcome Email',
    $generator->body([
        $generator->banner('https://example.com/logo.png', 600, 100, 'Logo'),
        $generator->section([
            $generator->h1('Welcome!'),
            $generator->p('Thank you for joining us.'),
            $generator->link('https://example.com', 'Visit our website')
        ]),
        $generator->footer('© 2024 Example Inc.')
    ])
);

// Generate individual components
$section = $generator->section([
    $generator->h2('Section Title'),
    $generator->p('Section content')
]);

// Generate columns layout
$columns = $generator->columns(
    $generator->p('Column 1'),
    $generator->p('Column 2'),
    $generator->p('Column 3')
);

// Generate card
$card = $generator->card([
    $generator->h3('Card Title'),
    $generator->p('Card content')
]);
```

## Implementation Notes (for Contributors)

### Email Address Validation

- Uses PHP's `filter_var()` with `FILTER_VALIDATE_EMAIL` for validation.
- Addresses are sanitized with `FILTER_SANITIZE_EMAIL` before validation.
- Normalization includes converting " at " and " dot " to "@" and "." for user-friendly input.

### MailboxList Parsing

- Handles comma-separated addresses that may span multiple lines.
- Uses a prefix mechanism to handle addresses split across commas.
- Automatically deduplicates addresses by email address (not by name).

### Email HTML Generation

- Uses table-based layouts for email compatibility (many email clients don't support modern CSS).
- Mobile styles are applied via media queries in a separate `<style>` block.
- Styles are defined as PHP arrays and converted to CSS via Elementary's style system.
- All links automatically open in new tabs (`target="_blank"`).

### Style System

- Styles cascade: `getStylesFor('h1', 'heading')` will merge styles from both 'h1' and 'heading' tags.
- Tag styles are applied in reverse order (last tag takes precedence).
- Custom styles can be passed to most methods via `$tagStyles` parameter.

## Testing & Quality

**Current Status:**
- Code quality: 2/5
- README quality: 2/5
- Documentation: 0/5 (no formal docs yet)
- Tests: 0/5 (no test suite yet)

**Testing Considerations:**
- `Mailbox` should be tested for:
  - Valid email address formats
  - Invalid email address handling
  - Name extraction from formatted strings
  - Normalization of " at " and " dot "
  - Domain extraction
  - String formatting

- `MailboxList` should be tested for:
  - Parsing comma-separated addresses
  - Handling multi-line addresses
  - Adding and removing mailboxes
  - Duplicate handling
  - Empty list behavior

- `Mail\Generator` should be tested for:
  - HTML document generation
  - Style application
  - Mobile responsive styles
  - All component methods
  - Tagged dependency handling

## Roadmap & Future Ideas

- **SMTP integration**: Add email sending capabilities via SMTP
- **Email templates**: Template system for reusable email layouts
- **Attachment handling**: Support for email attachments
- **Multipart messages**: Support for plain text and HTML alternatives
- **Email queue**: Queue management for batch sending
- **Email validation**: More sophisticated email validation (MX record checking, etc.)
- **Internationalization**: Support for internationalized email addresses
- **Email security**: SPF, DKIM, DMARC support
- **Testing utilities**: Email testing and preview tools

## References

- Package repository: https://github.com/decodelabs/relay
- Composer package: https://packagist.org/packages/decodelabs/relay
- Related packages:
  - `decodelabs/exceptional` — Exception handling
  - `decodelabs/tagged` — HTML markup generation (required for Generator)
  - `decodelabs/nuance` — Debugging and inspection (optional)
  - `decodelabs/elementary` — Style management (used internally)
  - `decodelabs/telegraph` — Mailing list manager (may use Relay types)

