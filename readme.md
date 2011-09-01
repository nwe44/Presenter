# Presenter - a simple presentation system

This is a program to enable non-technical ftp users to create a collection of image slideshows with attached comments.

At my office there are a number of users who need to be able to create slideshows within our our domain. **This means a number of limitations:**

1. No code coding knowledge may be required for usage
2. No server technology (ie. ability to see/use .htaccess or databases) knowledge may be required
3. Needs to be locally hosted

Most web hosts provide a gui for password protecting a directory, so this is what I'm assuming people will use for security. I can't imagine there are many options given the above constraints.

## Installation

1. Upload index.php, the "contents" folder and the "_" folder to a public directory

Done.

### To create a presentation
Each sub-directory of "contents" constitutes a presentation, so,

1. Drop images into a sub-directory of the contents folder.
2. Create a text file (with any name) in to the folder created in step 1. The contents of this file will be parsed as Markdown, and the h1 element will name the presentation.


## Current status
This is more of a UX prototype than a polished piece of programming at the moment. I'm sure there are plenty of bugs, please add any you find to the issue queue. Expect much streamlining of the code. This was quickly mocked up one weekend, I'm sure some of the libraries used are overkill and they will eventually be pruned.

### Plans for v0.2

1. Add a dynamic .appcache document.
2. Produce a production version with inlined and minified css/js 
3. Simplify settings UI.