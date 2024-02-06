{{ highlight.markText |trim }}  // 这个会对内容进行合并

{{review.content | replace( r/(?<=^|\s)#([^\s]+)/g, '[[$1]]')}}  //将标签转换为双链