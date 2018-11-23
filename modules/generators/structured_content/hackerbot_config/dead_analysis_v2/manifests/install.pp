class dead_analysis_v2::install {

  $url_path = "http//:z.cliffe.schreuders.org/files/6543367533"
  # This file is just too big and binary to make sense to include in the git repo
  file {
      "/root/hda1.img":
          source => "$url_path/hda1.img"
  }
  file {
      "/root/md5s":
          source => "$url_path/md5s"
  }

}
