---
isbn: {{metaData.isbn}}
category: {{metaData.category}}
---
### 元数据
{{metaData.title}}
![ {{metaData.title}}|200]({{metaData.cover}})
书名::[[{{metaData.title}}-{{metaData.bookId}}]]
需求::{{metaData.title}}
书作者::{{metaData.author}}
简介::{{metaData.intro}}
出版时间::{{metaData.publishTime}}
ISBN::{{metaData.isbn}}
分类::{{metaData.category}}
出版社::[[{{metaData.publisher}}]]

### 本书评论
{% if bookReview.bookReviews %}{% for bookReview in bookReview.bookReviews %}
#### 书评 No.{{loop.index}} {{bookReview.mdContent | replace( r/(?<=^|\s)#([^\s]+)/g, '[[$1]]')}}
{% endfor%}{% endif %}

### 高亮划线
{% for chapter in chapterHighlights %}
#### {{chapter.chapterTitle}}
{% for highlight in chapter.highlights %}
- {{ highlight.markText |trim }}   {% endfor %}{% endfor %}

### 读书笔记
{% for chapter in bookReview.chapterReviews %}{% if chapter.reviews or chapter.chapterReview %}
#### {{chapter.chapterTitle}}{% if  chapter.chapterReviews %}{% for chapterReview in chapter.chapterReviews %}
#### 章节评论 No.{{loop.index}}
- {{chapterReview.content}}  {% endfor%}{%endif %}
{% if chapter.reviews %}{%for review in chapter.reviews %}
- {{review.abstract |trim }}  {{review.content | replace( r/(?<=^|\s)#([^\s]+)/g, '[[$1]]')}}
{% endfor %} {%endif %} {% endif %} {% endfor %}
