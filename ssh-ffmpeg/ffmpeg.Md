### 微信小程序语音 mp3格式 上传服务器并转码科大讯飞 || 百度语音识别 所需的 wav格式

##### ffmpeg-3.1 version 就行了

```
    [CentOS_7.4]Linux编译安装ffmpeg
    安装过程：
    1# 安装yasm
    
    1 # wget http://www.tortall.net/projects/yasm/releases/yasm-1.3.0.tar.gz
    2 # tar -zxvf yasm-1.3.0.tar.gz
    3 # cd yasm-1.3.0
    4 # ./configure
    5 # make && make install
    
    2下载安装源，配置，编译，安装，设置环境变量。
    
    1 # wget http://www.ffmpeg.org/releases/ffmpeg-3.1.tar.gz
    2 # tar -zxvf ffmpeg-3.1.tar.gz
    3 # cd ffmpeg-3.1
    4 # ./configure --prefix=/usr/local/ffmpeg
    5 # make && make install
    6
    7 等待安装完成...
    8
    9 # vi /etc/profile
    10 在最后PATH添加环境变量：
    11 PATH=$PATH:/usr/local/ffmpeg/bin
    12 export PATH
    13 保存退出
    14
    15 # source /ect/profile   设置生效
    16 # ffmpeg -version       查看版本
    
```
