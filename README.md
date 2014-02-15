SignTp
======

A PocketMine plugin that let you place a sign to teleport.

# Installation

1.  Drop it into your /plugins folder.
2.  Restart your server.

# Commands

| Command | Parameter | Description |
| :-----: | :-------: | :---------: |
| /st help/?| `[commandName]` | Show help (of `[commandName]`) |
| /st version | None | Show plugin version |
| /st signhelp | None | Show help of creating tpsigns |
| /st c/create | `<PointName>` [`<x>` `<y>` `<z>` `<world>`] | Create a point. Leave blank location for your own location |
| /st d/delete | `<PointName>` | Delete a point |
| /st l/list | None | List all points |
| /st i/info | `<PointName>` | Show information of `<PointName>` |
| /st tp | `<PointName>` `[PlayerName]` | Teleport `[PlayerName]`(blank for yourself) to `<PointName>` |

# Console commands

| Command | Parameter | Description |
| :-----: | :-------: | :---------: |
| /st reconfig | None | Reload the point data from /SignTp/point.yml |
| /st checkupd | None | Check update from server. |

# Tips

/plugins/SignTp/point.yml : Yaml of points.
/plugins/SignTp/lang.yml : Language confit.

# Others

Wiki page: https://github.com/sieneis-82/SignTp/wiki
BUG Report: http://found.dw.cn/42.php

Our website: http://www.dw.cn

Copyright 2007-2014 DreamWork Studio.



