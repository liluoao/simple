# 36.使用依赖管理——以Composer为例

### 依赖管理

Composer 不是一个包管理器。是的，它涉及 "packages" 和 "libraries"，但它在每个项目的基础上进行管理，在你项目的某个目录中（例如 `vendor`）进行安装。默认情况下它不会在全局安装任何东西。因此，这仅仅是一个依赖管理。

这种想法并不新鲜，Composer 受到了 node's [npm](http://npmjs.org/) 和 ruby's [bundler](http://gembundler.com/) 的强烈启发。而当时 PHP 下并没有类似的工具。

Composer 将这样为你解决问题：

a\) 你有一个项目依赖于若干个库。

b\) 其中一些库依赖于其他库。

c\) 你声明你所依赖的东西。

d\) Composer 会找出哪个版本的包需要安装，并安装它们（将它们下载到你的项目中）。



