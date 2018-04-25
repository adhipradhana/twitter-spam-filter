import tweepy
import json
import sys
import re
from flask import Flask, request

CONSUMER_KEY = ''
CONSUMER_SECRET = ''
ACCESS_TOKEN = ''
ACESSS_TOKEN_SECRET = ''


auth = tweepy.OAuthHandler(CONSUMER_KEY, CONSUMER_SECRET)
auth.set_access_token(ACCESS_TOKEN, ACESSS_TOKEN_SECRET)

api = tweepy.API(auth)

app = Flask(__name__)

def get_timeline(username) :
    timeline = api.user_timeline(username)
    json_tweets = []
    for tweet in timeline :
        json_tweet = {}
        json_tweet["id"] = tweet.id
        json_tweet["name"] = tweet.user.name
        json_tweet["username"] = tweet.user.screen_name
        json_tweet["text"] = tweet.text
        json_tweet["image"] = tweet.user.profile_image_url_https
        json_tweet["spam"] = False
        json_tweet["index"] = []
        json_tweets.append(json_tweet)
    return json_tweets

def spam_filters_insensitive(timeline, keyword, algorithm) :
    spam_keyword = keyword.lower()
    if (algorithm == 0) :
        for tweet in timeline :
            buff_tweet = tweet["text"].lower()
            list_index = bm_match(buff_tweet, spam_keyword)
            if (list_index) :
                tweet["spam"] = True
                tweet["index"] = list_index
                tweet["keyword_length"] = len(keyword)

    elif (algorithm == 1) :
        for tweet in timeline :
            buff_tweet = tweet["text"].lower()
            list_index = kmp_match(buff_tweet, spam_keyword)
            if (list_index) :
                tweet["spam"] = True
                tweet["index"] = list_index
                tweet["keyword_length"] = len(keyword)

    else :
        for tweet in timeline :
            spam_keyword = re.compile(keyword)
            iterator = spam_keyword.finditer(tweet['text'])
            list_index = []
            for match in iterator :
                index = match.span()
                list_index.append(index[0])
                keyword_length = index[1] - index[0]

            if (list_index) :
                tweet["spam"] = True
                tweet["index"] = list_index
                tweet['keyword_length'] = keyword_length

def spam_filters_sensitive(timeline, keyword, algorithm) :
    if (algorithm == 0) :
        for tweet in timeline :
            list_index = bm_match(tweet['text'], keyword)
            if (list_index) :
                tweet["spam"] = True
                tweet["index"] = list_index
                tweet["keyword_length"] = len(keyword)

    elif (algorithm == 1) :
        for tweet in timeline :
            list_index = kmp_match(tweet['text'], keyword)
            if (list_index) :
                tweet["spam"] = True
                tweet["index"] = list_index
                tweet["keyword_length"] = len(keyword)

    else :
        for tweet in timeline :
            spam_keyword = re.compile(keyword)
            iterator = spam_keyword.finditer(tweet['text'])
            list_index = []
            for match in iterator :
                index = match.span()
                list_index.append(index[0])
                keyword_length = index[1] - index[0]
                
            if (list_index) :
                tweet["spam"] = True
                tweet["index"] = list_index
                tweet['keyword_length'] = keyword_length

def bm_match(text, pattern):
    result = []
    m = len(pattern)
    n = len(text)
    last = initialized(text, n) 
    last = buildLast(last, pattern, m) 
    s = 0
    while(s <= n-m):
        j = m-1
        while j>=0 and pattern[j] == text[s+j]:
            j -= 1
        if j<0:
            result.append(s)
            s += (m-last[text[s+m]] if s+m<n else 1)
        else:
            s += max(1, j-last[text[s+j]])
    return result

def initialized(text, size):
    last = {}
    for i in range(size):
        last[text[i]] = -1;
    return last;

def buildLast(last, pattern, size):
    for i in range(size):
        last[pattern[i]] = i;
    return last

def kmp_match(text, pattern):
    result = []
    M = len(pattern)
    N = len(text)
    lps = [0]*M
    j = 0 
    compute_fail(pattern, M, lps)
    i = 0 
    while i < N:
        if pattern[j] == text[i]:
            i += 1
            j += 1

        if j == M:
         #   print("Posisi pattern = {}".format(i-j))
            result.append(i-j)
            j = lps[j-1]
        elif i < N and pattern[j] != text[i]:
            if j != 0:
                j = lps[j-1]
            else:
                i += 1
    return result

def compute_fail(pattern, M, lps):
    len = 0 
    lps[0]
    i = 1
    while i < M:
        if pattern[i]==pattern[len]:
            len += 1
            lps[i] = len
            i += 1
        else:
            if len != 0:
                len = lps[len-1]
            else:
                lps[i] = 0
                i += 1

@app.route('/', methods = ['POST'])
def main() :
    form = dict(request.form)

    username = form["username"][0].replace('@','')
    keyword = form["keyword"][0]
    algorithm = int(form["algorithm"][0])
    if 'case-sensitive' in form.keys() :
        case_sensitive = True
    else :
        case_sensitive = False

    post = {}
    post['keyword'] = keyword
    try :
        json_tweets = get_timeline(username)
        if case_sensitive :
            spam_filters_sensitive(json_tweets, keyword, algorithm)
        else :
            spam_filters_insensitive(json_tweets, keyword, algorithm)
        post['data'] = json_tweets
        post['empty'] = False
    except :
        post['empty'] = True

    return json.dumps(post)

if __name__ == '__main__':
    app.run(debug = True, port=1111, host='0.0.0.0')
