Basic ACL for PYDIO repositories

On a repository, enables to:
- forbid access to a file or folder (recursively for folders)
- grant read only access on a file or folder (recursively for folders)
- grant "unchanged" access (if read only on the repo, still read only, if read/write on the repo, still read/write) (recursively for folders)

As you can grant it on each file or folder, it is possible to have a folder in a read-only folder and grant "unchanged" to the sub-folder so that someone with write accès on the rest of the repo will get it's write access back for the subfolder.
