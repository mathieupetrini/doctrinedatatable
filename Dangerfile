# Sometimes it's a README fix, or something like that - which isn't relevant for
# including in a project's CHANGELOG for example
declared_trivial = gitlab.mr_title.include? "#trivial"

# Make it more obvious that a PR is a work in progress and shouldn't be merged yet
warn("MR is classed as Work in Progress") if gitlab.mr_title.include? "[WIP]"
warn("MR is classed as Work in Progress") if gitlab.mr_title.include? "WIP:"

# ENSURE THAT ALL MRS HAVE AN ASSIGNEE.
warn "This MR does not have any assignees yet." unless gitlab.mr_json["assignee"]

# HIGHLIGHT WITH A CLICKABLE LINK IF A PACKAGE.JSON IS CHANGED.
warn "#{gitlab.html_link("package.json")} was edited." if git.modified_files.include? "package.json"
warn "#{gitlab.html_link("composer.json")} was edited." if git.modified_files.include? "composer.json"
warn "#{gitlab.html_link("Gemfile")} was edited." if git.modified_files.include? "Gemfile"

# CHANGELOG
if !(git.modified_files.include? "changelogs/CHANGELOG.md") && (git.added_files.length === 0 || git.added_files.select{|food| food.match(/changelogs\/unreleased\/.*\.yml/i)}.length === 0) && !declared_trivial
  failure("Please include a CHANGELOG entry.")
end