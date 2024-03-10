---
标题: "{{title}}"
创建时间: {{ createat}}
更新时间: {{updateat}}
作者: {{author}}
标签: {{mergedtags}}
笔记类型: {{notetype}}
笔记ID: {{noteid}}
全域名: {{domain}}
母域名: {{domain2}}
url: "{{url}}"
wucai_url: {{wucaiurl}}

highlightcount: {{highlightcount}}

{% if pagenote %}pagenote: 1 {% endif %}

{% if ispagemirror %}pagemirror: 1 {% endif %}

---
[[{{ createat_ts | date("YYYY-MM-DD") }}]] 
{% if pagenote %}
### 页面笔记
{% block pagenote %}
{{pagenote }}
{% endblock %}
{% endif %}
{% block highlights %}
{% for item in highlights %}
{% if item.imageUrl%}
{%if item.annonation%}
{{item.annonation}}
{% endif %}
![]({{ item.imageUrl }})
{% else %}
{%if item.annonation%}{{item.annonation}}{% endif %}{{item.note }}
{% endif%}
{% endfor %}

{% endblock %}

{% if mdcontent %}
### 全文剪藏
{% block mdcontent %}
{{mdcontent}}
{% endblock %}
{% endif %}



