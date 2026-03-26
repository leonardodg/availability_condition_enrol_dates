
# 🚀 Plugin Availability Condition: Enrol Dates - by LeoDG - Moodle 4.5.1+
## Description new Plugin to implemment


This plugin allows teachers to restrict access to activities or sections based on user enrollment dates or course dates. Ideal for creating automated learning paths in Ivana Academy. It is the Documentaion about new Plugin Availability Condition Enrol Dates.

Folder Path this plugin: availability/condition/enrol_dates

Object: Create Availability Conditions allow teachers to restrict an activity or section so that only certain users can access it.


> Form to conditions are:

- Select DIRECTION = ['before','after']
- Select Time Value Check = ["coursetimestart","coursetimeend","enroltimestart","enroltimeend"]
- Input Time Number = Campus insert value in number to comparer
- Select Time Period = ["hours, "days", "months"]

# 📸 Demonstração
[images]

# ✨ Features
- Time Direction: Restrict before or after a specific date.
- Dynamic Triggers:
    * Course Start/End Date.
    * Student Enrollment Start/End Date.
- Flexible Periods: Configure in Hours, Days, or Months.

## Rules and Conditions:

- if before: allow access in context period TimeValueCheck
    Example: 10 Days before EnrolTimeEnd
        - "10" = number value insert in input
        - "Days" = Time Period selected
        - "EnrolTimeEnd" =  Time Value Check Selected
        - "Before" = Direction Selected

    Calcule: EnrolTimeEnd 10/24/2028 - 10 Days = Less because is before

- if after: allow access in context period TimeValueCheck
    Example: 1 Month after EnrolTimeStart
        - "1" = number value insert in input
        - "Month" = Time Period selected
        - "EnrolTimeStart" =  Time Value Check Selected
        - "after" = Direction Selected

    Calcule: EnrolTimeStart 10/24/2028 + 1 Month = More because is after

Database values:
  - CourseTimeStart - table mdl_courses - campus timestart
  - CourseTimeEnd - table mdl_courses - campus timeend
  - EnrolTimeStart - table mdl_enrolmment - campus timestart
  - EnrolTimeEnd - table mdl_enrolmment - campus timeend


## Prerequisites
- [Git](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git)
- [Moodle](https://moodledev.io/docs/4.5)

### Plugin Moodle DOC
 - [Moodle Availability](https://moodledev.io/docs/4.5/apis/plugintypes/availability)


## DEVELOPERS

### RUN NPM DOCKER
```
apt-get update
apt-get install npm
npm install -g n
n 20.11.0
npm install -g grunt-cli
hash -r
npm install
npx grunt amd
```


### BUILD JS COMMANDS

> Build by Script:
```
    #!/bin/bash
    # Build script for availability_enrol_dates YUI plugin.
    # Run this after editing yui/src/form/js/form.js

    PLUGIN_DIR="$(cd "$(dirname "$0")" && pwd)"
    SRC="$PLUGIN_DIR/yui/src/form/js/form.js"
    BUILD_DIR="$PLUGIN_DIR/yui/build/moodle-availability_enrol_dates-form"
    MODULE="moodle-availability_enrol_dates-form"
    REQUIRES='"base", "node", "event", "moodle-core_availability-form"'

    # Wrap source in YUI.add(...)
    WRAPPED=$(printf "YUI.add('%s', function (Y, NAME) {\n\n%s\n\n}, '@VERSION@', {\"requires\": [%s]});\n" \
        "$MODULE" "$(cat "$SRC")" "$REQUIRES")

    echo "$WRAPPED" > "$BUILD_DIR/$MODULE-debug.js"
    echo "$WRAPPED" > "$BUILD_DIR/$MODULE.js"
    echo "$WRAPPED" > "$BUILD_DIR/$MODULE-min.js"
```

> Build by Grunt ( Recommended )
- Just Folder project:
```
npx grunt yui --path=availability/condition/enrol_dates -debug.js
php admin/cli/purge_caches.php
```

- FULL JS Moodle
```
npx grunt amd
php admin/cli/purge_caches.php
```
## 🛠 Project Structure

```
 availability/condition/enrol_dates
├── classes
│   ├── condition.php
│   ├── frontend.php
├── lang
│   └── en
│       └── availability_enrol_dates.php
├── version.php
└── yui
    ├── build
    │   └── moodle-availability_enrol_dates-form
    │       ├── moodle-availability_enrol_dates-form-debug.js
    │       ├── moodle-availability_enrol_dates-form-min.js
    │       └── moodle-availability_enrol_dates-form.js
    └── src
        └── form
            ├── build.json
            ├── js
            │   └── form.js
            └── meta
                └── form.json
```

 ## 🤝 Contributing
 <img src="https://avatars.githubusercontent.com/u/1678290?s=400&u=2f875356b82f055057b6e9679c0b66001b9b29f9&v=4" title="LeoDG" >


 ## 📄 License
 This project is licensed under version 3 of the GNU General Public License *(GPL v3+)* - see the [LICENSE](LICENSE) file for details. The same licence of Moodle is provided freely as open source software.

 ## 📮 Contact
LeonardoDG - [@le0dg](https://www.linkedin.com/in/le0dg)

Github Repository Link: [https://github.com/leonardodg/website.git](https://github.com/leonardodg/website.git)

GH-Pages Link: [https://leonardodg.github.io/website](https://leonardodg.github.io/website)

Website Link: [https://leodg.dev](https://leodg.dev)