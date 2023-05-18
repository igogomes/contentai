## CONTENTS OF THIS FILE

* Introduction
* Requirements
* Recommended Modules
* Installation
* Configuration

## INTRODUCTION

Content AI is a powerful module that leverages the OpenAI platform to automatically generate high-quality content. It can be used to produce SEO content such as titles, keywords and meta descriptions for articles, provide translated content for articles and pages, as well as for other purposes.

* For a full description of the module visit: <https://www.drupal.org/project/contentai>

* To submit bug reports and feature suggestions, or to track changes visit: <https://www.drupal.org/project/issues/contentai>

## REQUIREMENTS

This module requires the following modules:

* Metatag - https://www.drupal.org/project/metatag
* OpenAI / ChatGPT / AI Search Integration - https://www.drupal.org/project/openai

## RECOMMENDED MODULES

* No extra module is required.

## INSTALLATION

* Install the Content AI module as you would normally install a contributed Drupal module. Visit <https://www.drupal.org/docs/extending-drupal/installing-modules> for further information.

## CONFIGURATION

### SEO content generation

The module provides several settings, which you can access via yoursite/admin/config/contentai/settings:

* Option: this determines the type of information that will be generated automatically with the support of GPT. The initial options available include Title, Keywords, and Description;

* Model: this refers to the version of GPT that will be used for generating the content. Currently, text-babbage-001, text-curie-001, and text-davinci-003 are available, but more versions may be added in the future;

* Temperature: this setting controls the tone of the content that is automatically generated. You can adjust it to make the tone more serious or friendly, depending on your needs;

* Meta tag field selector: this setting determines the ID of the field that will receive the automatically generated content for an option (Title, Keyword, or Description). For example, the field that will receive the automatically generated content for the title is #edit-field-description-0-basic-title on the article creation page;

* Question: this is the question that will be asked to GPT to generate the title, keywords, description, or other information automatically. In the question, the shortcode {body} is used to represent the text of the article, so the question will also be asked considering this text.

Once you have configured the settings, you can simply go to the article edition page (yoursite/node/node-number/edit) and select one or more options in the Meta Content AI section (Description, Title, or Keywords). Click on the Generate button, and the content will be automatically generated based on the settings you have specified, as well as the article text.

The generated content will be inserted into the page title, description, and keywords fields under Basic tags in the Description section. You can find the Meta Content AI and Description sections in the right column of the article creation/edit page.

It is important to note that the Meta Content AI section will only be displayed after you have saved the initial text of the article (in the Body field), as this content will be used for automatic generation through GPT. With Content AI, you can save time and effort while producing high-quality content that meets your needs.