# WP Forge Command

A zero-configuration* scaffolding tool built as a WP-CLI package.

*This tool does assume that you have a Git repository containing [scaffolding templates](#scaffolding-templates) that
follow a specific convention.

## Installation

1. [Install WP-CLI](https://wp-cli.org/#installing)

2. Install this WP-CLI package:

```shell
wp package install wp-forge/wp-cli-forge-command
```

## Usage

From your project root, run the `init` command to set up a project-level configuration file.<br>
_*Technically, this step is optional. However, it helps to eliminate some prompts as you run make commands._

```shell
wp forge init
```

Run the `make` command to scaffold a new entity such as a plugin, theme, etc.

```shell
wp forge make <name>
```

## Advanced Usage

Clone a Git repository containing [scaffolding templates](#scaffolding-templates) so they will be locally available to
the tool.

```shell
wp forge clone <repository_url>
```

When cloning a repository, you can optionally provide a name. This allows you to utilize multiple repositories
containing scaffolding templates from various sources. If you do not set a name, the system will use the name "default"
automatically.

```shell
wp forge clone <repository_url> --as=<name>
```

To scaffold using a template from a named repository, just prefix the entity name with your custom namespace.

For example, if you set the name to be `company`, and you wanted to scaffold a `wp-plugin`, then you would run this
command:

```shell
wp forge make company:wp-plugin
```

This will ensure that the repository containing the `company` templates will be checked for the `wp-plugin` scaffolding.
In the event that you have multiple template sources configured and the requested template cannot be found under the
requested namespace, the tool will ask you if you want to check the other template sources for that template.

You can also use a path to leverage templates found nested in other folders.

```shell
wp forge make company:github-actions/lint-php
```

The above command would look in the `~/.wp-cli/templates/company` folder for the template in
the `github-actions/lint-php` directory.

## Documentation

All commands are self-documented by the tool. Simply type an available command followed by the `--help` flag for more
details.

**Get high-level documentation on available commands:**

```shell
wp forge --help
```

**Get documentation for a specific command:**

```shell
wp forge config --help
```

## Scaffolding Templates

In order to use this tool, you must first have a Git repository where you will host your scaffolding templates.

**Let's get started!**

> **Step 1:** Create a [new Git repository](https://github.com/new).

> **Step 2:** Create a folder in the repository for each thing you will want to scaffold. The name of the folder is the name you will use with the `make` command.

Examples of things you might want to scaffold:

- WordPress plugins
- WordPress themes
- WordPress sites
- Custom post types
- GitHub actions
- Other custom code you use frequently

> **Step 3:** Make sure you have a `config.json` file in the template folder. This will tell the CLI what to do with your template.

### Config Examples

A simple `config.json` file might look like this:

```json
{
  "directives": [
    {
      "action": "copy",
      "from": "lint-php.yml",
      "to": ".github/workflows/lint-php.yml",
      "relativeTo": "projectRoot"
    }
  ]
}
```

This would copy the `lint-php.yml` file from the template folder to the `.github/workflows/lint-php.yml` file relative
to the project root. You can provide multiple copy directives to copy not only files, but also entire directories. If
you want the path to be relative to the current directory where the CLI tool is being run, then just leave off
the `relativeTo` property or set its value to `workingDir`.

It is very common that you will want to replace placeholders in your templates. To facilitate this, you must first
collect the required information from the user.

You can add a `prompts` section to trigger these data requests in the CLI:

```json
{
  "prompts": [
    {
      "message": "What is your first name?",
      "name": "first_name",
      "type": "input"
    },
    {
      "message": "What country are you in?",
      "name": "country",
      "type": "input",
      "default": "United States"
    },
    {
      "message": "What is your favorite ice cream?",
      "name": "ice_cream",
      "type": "radio",
      "options": [
        "Chocolate",
        "Vanilla",
        "Strawberry"
      ]
    },
    {
      "message": "Select one or more taxonomies",
      "name": "taxonomies",
      "type": "checkboxes",
      "options": [
        "Categories",
        "Tags"
      ]
    }
  ]
}
```

With these prompts defined, you can now use the `name` field as a [Mustache](https://mustache.github.io/) placeholder in
any template file. You can also reference the name of any property from the project configuration file in your templates
without needing to prompt the user.

You can have a template leverage other templates by using the `runCommand` directive and calling the `make` command:

```json
{
  "directives": [
    {
      "action": "runCommand",
      "command": "wp forge make github-actions/lint-js"
    },
    {
      "action": "runCommand",
      "command": "wp forge make github-actions/lint-php"
    },
    {
      "action": "runCommand",
      "command": "wp forge make github-actions/lint-yml"
    }
  ]
}
```
