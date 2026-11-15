# Modules

## What is a Module?

A module is a self-contained unit of functionality within a SaQle application. It encapsulates the components, logic, resources, and configuration required to implement a particular functional area of an application.

Modules help organize an application into smaller, manageable units, each responsible for a specific concern. Rather than placing all application functionality in a single application structure, you can divide it into modules based on business domains or features.

## Why use modules?

Modules provide several benefits:

1. **Separation of concerns**: Each module focuses on a specific functional area.
2. **Maintainability**: Related functionality is grouped together, making the application easier to understand and maintain.
3. **Scalability**: New functionality can be introduced by adding modules without unnecessarily complicating existing ones.
4. **Reusability**: Modules can potentially be reused across different applications.
5. **Team collaboration**: Different developers can work on different modules with fewer conflicts.

## Example Module Structure

For illustration, a SaQle application could have the following structure:

```text
app/
├── config/
├── public/
└── src/
    ├── Bootstrap/
    ├── Databases/
    └── Modules/
        ├── Auth/
        │   ├── Components/
        │   ├── Middleware/
        │   ├── Validators/
        │   ├── Guards/
        │   └── Contracts/
        ├── Billing/
        └── Inventory/
```

::: tip
Refer to [Project Structure](/getting-started/structure.md) section to learn more about these directories.
:::