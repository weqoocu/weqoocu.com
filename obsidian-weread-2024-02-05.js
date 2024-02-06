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
#### 书评 No.{{loop.index}} {{bookReview.mdContent}}^{{bookReview.mdContent | replace(r/[\\]/g,"")}}
⏱ [[{{bookReview.createTime.slice(0, 10)}}]]{{bookReview.createTime.slice(10, 16)}}
{% endfor%}{% endif %}

### 高亮划线
{% for chapter in chapterHighlights %}
#### {{chapter.chapterTitle}}
{% for highlight in chapter.highlights %}
{% if highlight.reviewContent %}{% else %}
- 📌 {{ highlight.markText |trim }} ^{{highlight.chapterUid}}-{{highlight.range}}
    - ⏱ [[{{highlight.createTime.slice(0, 10)}}]]{{highlight.createTime.slice(10, 16)}} {% endif %} {% endfor %}{% endfor %}
### 读书笔记
{% for chapter in bookReview.chapterReviews %}{% if chapter.reviews or chapter.chapterReview %}
#### {{chapter.chapterTitle}}
{% if  chapter.chapterReviews %}{% for chapterReview in chapter.chapterReviews %}
#### 章节评论 No.{{loop.index}}
- {{chapterReview.content}} ^{{chapterReview.reviewId}}
    - ⏱ [[{{chapterReview.createTime.slice(0, 10)}}]]{{chapterReview.createTime.slice(10, 16)}} {% endfor%}{%endif %}{% if chapter.reviews %}{%for review in chapter.reviews %}
#### 划线评论
- 📌 {{review.abstract |trim }}  ^{{review.reviewId}}
    - {{ review.content | replace(r/#(.*?)[\s\b]/g, "[[\\1]]") }}
    - ⏱ [[{{review.createTime.slice(0, 10)}}]]{{review.createTime.slice(10, 16)}} 
{% endfor %} {%endif %} {% endif %} {% endfor %}
