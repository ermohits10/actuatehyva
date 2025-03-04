# Frontend API - V1

With Hyvä Checkout, in addition to our primarily Magewire/backend-driven systems and APIs, we also emphasize the
importance of an API to seamlessly integrate frontend functionalities. This API, aptly named our "Frontend API," is
designed to empower developers with a richer experience, enabling extensive customization and facilitating the creation
of new features.

## Directories

The folder structure of the frontend API can be overwhelming when working with it for the first time. It's a concept to create
a plugin pattern in terms of adding custom code, without having to overwrite @internal marked templates.

### Alpine JS

The alpinejs directory is designated for storing individual files, each housing a single function.
These functions are intended to be utilized within your AlpineJS-driven components.
This modular approach enhances maintainability, code organization, and re-usability within your project.

### Directive (deprecated)

The directive folder was initially designed to house template files containing custom AlpineJS or Magewire directives for
enhanced functionality. However, due to evolving project requirements and optimizations, the use of this directory
is now considered `@deprecated`.

### Evaluation

The evaluation folder serves as a dedicated space for storing supporting files related to the Evaluation API.
This section provides templates essential for presenting specific User Experience (UX) elements tailored for
evaluation results that necessitate user interaction.

### Best practices

Each frontend API object is accompanied by its own initialization phtml file, which may or may not require additional
supporting phtml files. In cases where additional files are necessary, they are stored in a corresponding child
directory named after the API object.

For instance, the `Hyva_Checkout::page/js/api/v1/evaluation` directory is associated with `Hyva_Checkout::page/js/api/v1/init-evaluation.phtml`

## Files

### Initialization

For each section within the JS API, there exists an initialization file. These files encapsulate crucial code specific
to the respective section, eliminating the need to include that code directly within the API section itself.
This separation enhances the cleanliness of the overall API implementation.

By housing section-specific code in dedicated init files, the API structure remains organized and modular.
This approach also ensures that the associated code executes with priority, retaining control over functionalities
compared to pieces of code residing in the designated after container. The result is a streamlined and maintainable
API architecture, offering a clear distinction between section-specific behaviors and shared functionalities within
the after container.

#### After containers

Each API initialization file includes an after container, providing developers with the opportunity to incorporate
additional behavior at specific points within the document object model (DOM).
This allows for seamless extension and customization of functionality, enabling developers to enhance the application's
behavior precisely where it's needed.
