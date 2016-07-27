--
-- PostgreSQL database dump
--

-- Dumped from database version 9.5.3
-- Dumped by pg_dump version 9.5.3

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: forgotten_passwords; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE forgotten_passwords (
    id integer NOT NULL,
    subscriber_id integer NOT NULL,
    reminder character varying(141) NOT NULL,
    reminded_at timestamp without time zone NOT NULL,
    used boolean DEFAULT false NOT NULL
);


ALTER TABLE forgotten_passwords OWNER TO postgres;

--
-- Name: forgotten_passwords_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE forgotten_passwords_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE forgotten_passwords_id_seq OWNER TO postgres;

--
-- Name: forgotten_passwords_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE forgotten_passwords_id_seq OWNED BY forgotten_passwords.id;


--
-- Name: page_visits; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE page_visits (
    id integer NOT NULL,
    page_url character varying NOT NULL,
    visited_at timestamp without time zone NOT NULL
);


ALTER TABLE page_visits OWNER TO postgres;

--
-- Name: page_visits_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE page_visits_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE page_visits_id_seq OWNER TO postgres;

--
-- Name: page_visits_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE page_visits_id_seq OWNED BY page_visits.id;


--
-- Name: pages; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE pages (
    url character varying NOT NULL,
    content text NOT NULL
);


ALTER TABLE pages OWNER TO postgres;

--
-- Name: part_visits; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE part_visits (
    id integer NOT NULL,
    part_id integer NOT NULL,
    visited_at timestamp without time zone NOT NULL
);


ALTER TABLE part_visits OWNER TO postgres;

--
-- Name: part_visits_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE part_visits_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE part_visits_id_seq OWNER TO postgres;

--
-- Name: part_visits_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE part_visits_id_seq OWNED BY part_visits.id;


--
-- Name: parts; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE parts (
    id integer NOT NULL,
    page_url character varying NOT NULL,
    expression character varying NOT NULL,
    content text NOT NULL
);


ALTER TABLE parts OWNER TO postgres;

--
-- Name: parts_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE parts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE parts_id_seq OWNER TO postgres;

--
-- Name: parts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE parts_id_seq OWNED BY parts.id;


--
-- Name: subscribers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE subscribers (
    id integer NOT NULL,
    email character varying NOT NULL,
    password character varying NOT NULL
);


ALTER TABLE subscribers OWNER TO postgres;

--
-- Name: subscribers_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE subscribers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE subscribers_id_seq OWNER TO postgres;

--
-- Name: subscribers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE subscribers_id_seq OWNED BY subscribers.id;


--
-- Name: subscriptions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE subscriptions (
    id integer NOT NULL,
    subscriber_id integer NOT NULL,
    part_id integer NOT NULL,
    "interval" character varying(10) NOT NULL
);


ALTER TABLE subscriptions OWNER TO postgres;

--
-- Name: subscriptions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE subscriptions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE subscriptions_id_seq OWNER TO postgres;

--
-- Name: subscriptions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE subscriptions_id_seq OWNED BY subscriptions.id;


--
-- Name: verification_codes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE verification_codes (
    id integer NOT NULL,
    subscriber_id integer NOT NULL,
    code character varying(91) NOT NULL,
    used boolean DEFAULT false NOT NULL,
    used_at timestamp without time zone NOT NULL
);


ALTER TABLE verification_codes OWNER TO postgres;

--
-- Name: verification_codes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE verification_codes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE verification_codes_id_seq OWNER TO postgres;

--
-- Name: verification_codes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE verification_codes_id_seq OWNED BY verification_codes.id;


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY forgotten_passwords ALTER COLUMN id SET DEFAULT nextval('forgotten_passwords_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY page_visits ALTER COLUMN id SET DEFAULT nextval('page_visits_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY part_visits ALTER COLUMN id SET DEFAULT nextval('part_visits_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY parts ALTER COLUMN id SET DEFAULT nextval('parts_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY subscribers ALTER COLUMN id SET DEFAULT nextval('subscribers_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY subscriptions ALTER COLUMN id SET DEFAULT nextval('subscriptions_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY verification_codes ALTER COLUMN id SET DEFAULT nextval('verification_codes_id_seq'::regclass);


--
-- Data for Name: forgotten_passwords; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY forgotten_passwords (id, subscriber_id, reminder, reminded_at, used) FROM stdin;
\.


--
-- Name: forgotten_passwords_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('forgotten_passwords_id_seq', 1, true);


--
-- Data for Name: page_visits; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY page_visits (id, page_url, visited_at) FROM stdin;
1	http://facedown.cz	2016-07-24 18:05:52
2	http://facedown.cz	2016-07-26 22:08:00
3	http://facedown.cz	2016-07-27 19:24:51
\.


--
-- Name: page_visits_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('page_visits_id_seq', 3, true);


--
-- Data for Name: pages; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY pages (url, content) FROM stdin;
www.google.com	google
www.facedown.cz	seznam
http://facedown.cz	<!DOCTYPE html>\n<html lang="cs-cz"><head><meta charset="UTF-8"><meta name="robots" content="index, follow"><link href="/images/favicon32.png" rel="icon" type="image/png"><title>Facedown</title><meta name="description" content="PHP nad&scaron;enec a fanatik v&scaron;eho, co se t&yacute;k&aacute; objektov&#283; orientovan&eacute;ho programov&aacute;n&iacute;."><meta name="author" content="Facedown"><meta name="viewport" content="width=device-width, initial-scale=1"><link href="https://fonts.googleapis.com/css?family=Open+Sans&amp;subset=latin,latin-ext" rel="stylesheet" type="text/css"><link rel="stylesheet" href="/css/style.css" type="text/css"><link rel="stylesheet" href="/css/github.css" type="text/css"></head><body>\n<div id="wrap">\n    <nav class="navbar navbar-default navbar-static-top"><div class="container">\n            <div class="navbar-header">\n                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">\n                    <span class="sr-only">Toggle navigation</span>\n                    <span class="icon-bar"></span>\n                    <span class="icon-bar"></span>\n                    <span class="icon-bar"></span>\n                </button>\n                <a class="navbar-brand" title="Facedown" href="/"><strong>Facedown</strong></a>\n            </div>\n            <div id="navbar" class="navbar-collapse collapse">\n                <ul class="nav navbar-nav"><li title="&#268;l&aacute;nky"><a href="/clanky/">&#268;l&aacute;nky</a></li>\n                    <li title="O mn&#283;"><a href="/o-mne/">O mn&#283;</a></li>\n                    <li title="Kontakt"><a href="/kontakt/">Kontakt</a></li>\n                        <li title="P&#345;ihl&aacute;sit se"><a href="/prihlasit/">P&#345;ihl&aacute;sit se</a></li>\n                </ul></div>\n        </div>\n    </nav><div class="container">\n        <div class="row">\n        </div>\n<div class="text-center">\n\t\n\t<h1>Facedown</h1>\n\t<h2><a href="https://secure.php.net/" title="PHP official">PHP</a> nad&scaron;enec a fanatik v&scaron;eho, co se t&yacute;k&aacute; objektov&#283; orientovan&eacute;ho programov&aacute;n&iacute;.</h2>\n\t<a href=""><img width="180" height="180" id="facedown-avatar" src="/images/facedown.png" alt="facedown-avatar" title="Facedown"></a>\n\t<div id="social-icons">\n\t\t<a class="btn btn-social-icon btn-facebook" href="https://www.facebook.com/domca.klapuch" title="Facebook" rel="nofollow">\n\t\t\t<span class="fa fa-facebook"></span>\n\t\t</a>\n\t\t<a href="https://plus.google.com/u/0/+DominikKlapuchFacedown/posts" target="_blank" class="btn btn-social-icon btn-google-plus" title="Google +">\n\t\t\t<span class="fa fa-google-plus"></span>\n\t\t</a>\n\t\t<a href="https://twitter.com/klapuchdominik" target="_blank" class="btn btn-social-icon btn-twitter" title="Twitter">\n\t\t\t<span class="fa fa-twitter"></span>\n\t\t</a>\n\t\t<a href="https://bitbucket.org/facedown/" target="_blank" class="btn btn-social-icon btn-bitbucket" title="Bitbucket">\n\t\t<span class="fa fa-bitbucket"></span>\n\t</a>\n\t<a href="https://github.com/klapuch" target="_blank" class="btn btn-social-icon btn-github" title="GitHub">\n\t\t<span class="fa fa-github"></span>\n\t</a>\n\t<a href="https://www.linkedin.com/profile/view?id=312784923" target="_blank" class="btn btn-social-icon btn-linkedin" title="LinkedIn">\n\t\t<span class="fa fa-linkedin"></span>\n\t</a>\n</div>\n</div>    </div>\n</div>\n<div id="footer">\n    <div class="container">\n        <p class="muted credit text-center">\n            Vytvo&#345;il <a href="https://www.github.com/klapuch" title="Facedown">Facedown</a>\n        </p>\n    </div>\n</div>\n<script async src="/js/script.js" type="text/javascript"></script><script async src="/js/highlight.pack.js"></script></body></html>\n
\.


--
-- Data for Name: part_visits; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY part_visits (id, part_id, visited_at) FROM stdin;
1	3	2017-01-01 01:01:00
2	3	2017-01-01 01:01:00
3	3	2017-01-01 01:01:00
4	3	2017-01-01 01:01:00
5	3	2017-01-01 01:01:00
6	3	2017-01-01 01:01:00
7	3	2016-07-26 22:09:06
8	4	2016-07-27 19:24:51
\.


--
-- Name: part_visits_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('part_visits_id_seq', 8, true);


--
-- Data for Name: parts; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY parts (id, page_url, expression, content) FROM stdin;
1	www.google.com	//h1	content
3	http://facedown.cz	//h1	<h1>Facedown</h1>
4	http://facedown.cz	//h2	<h2><a href="https://secure.php.net/" title="PHP official">PHP</a> nadšenec a fanatik všeho, co se týká objektově orientovaného programování.</h2>
\.


--
-- Name: parts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('parts_id_seq', 4, true);


--
-- Data for Name: subscribers; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY subscribers (id, email, password) FROM stdin;
1	xcvxcv	xcvxcv
\.


--
-- Name: subscribers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('subscribers_id_seq', 9, true);


--
-- Data for Name: subscriptions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY subscriptions (id, subscriber_id, part_id, "interval") FROM stdin;
11	1	4	PT32M
\.


--
-- Name: subscriptions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('subscriptions_id_seq', 11, true);


--
-- Data for Name: verification_codes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY verification_codes (id, subscriber_id, code, used, used_at) FROM stdin;
\.


--
-- Name: verification_codes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('verification_codes_id_seq', 1, false);


--
-- Name: forgotten_passwords_ID; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY forgotten_passwords
    ADD CONSTRAINT "forgotten_passwords_ID" PRIMARY KEY (id);


--
-- Name: forgotten_passwords_reminder; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY forgotten_passwords
    ADD CONSTRAINT forgotten_passwords_reminder UNIQUE (reminder);


--
-- Name: page_visits_ID; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY page_visits
    ADD CONSTRAINT "page_visits_ID" PRIMARY KEY (id);


--
-- Name: pages_url; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY pages
    ADD CONSTRAINT pages_url PRIMARY KEY (url);


--
-- Name: part_visits_ID; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY part_visits
    ADD CONSTRAINT "part_visits_ID" PRIMARY KEY (id);


--
-- Name: parts_ID; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY parts
    ADD CONSTRAINT "parts_ID" PRIMARY KEY (id);


--
-- Name: parts_page_url_expression; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY parts
    ADD CONSTRAINT parts_page_url_expression UNIQUE (page_url, expression);


--
-- Name: subscribed_parts_ID; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY subscriptions
    ADD CONSTRAINT "subscribed_parts_ID" PRIMARY KEY (id);


--
-- Name: subscribed_parts_subscriber_id_part_id; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY subscriptions
    ADD CONSTRAINT subscribed_parts_subscriber_id_part_id UNIQUE (subscriber_id, part_id);


--
-- Name: subscribers_ID; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY subscribers
    ADD CONSTRAINT "subscribers_ID" PRIMARY KEY (id);


--
-- Name: subscribers_email; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY subscribers
    ADD CONSTRAINT subscribers_email UNIQUE (email);


--
-- Name: verification_codes_ID; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY verification_codes
    ADD CONSTRAINT "verification_codes_ID" PRIMARY KEY (id);


--
-- Name: verification_codes_code; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY verification_codes
    ADD CONSTRAINT verification_codes_code UNIQUE (code);


--
-- Name: verification_codes_subscriber_id; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY verification_codes
    ADD CONSTRAINT verification_codes_subscriber_id UNIQUE (subscriber_id);


--
-- Name: forgotten_passwords_subscriber_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX forgotten_passwords_subscriber_id ON forgotten_passwords USING btree (subscriber_id);


--
-- Name: page_visits_page_url; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX page_visits_page_url ON page_visits USING btree (page_url);


--
-- Name: part_visits_part_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX part_visits_part_id ON part_visits USING btree (part_id);


--
-- Name: subscribed_parts_part_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX subscribed_parts_part_id ON subscriptions USING btree (part_id);


--
-- Name: forgotten_passwords_subscriber_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY forgotten_passwords
    ADD CONSTRAINT forgotten_passwords_subscriber_id_fkey FOREIGN KEY (subscriber_id) REFERENCES subscribers(id) ON DELETE CASCADE;


--
-- Name: page_visits_page_url_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY page_visits
    ADD CONSTRAINT page_visits_page_url_fkey FOREIGN KEY (page_url) REFERENCES pages(url) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: part_visits_part_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY part_visits
    ADD CONSTRAINT part_visits_part_id_fkey FOREIGN KEY (part_id) REFERENCES parts(id) ON DELETE CASCADE;


--
-- Name: parts_page_url_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY parts
    ADD CONSTRAINT parts_page_url_fkey FOREIGN KEY (page_url) REFERENCES pages(url) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: subscribed_parts_part_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY subscriptions
    ADD CONSTRAINT subscribed_parts_part_id_fkey FOREIGN KEY (part_id) REFERENCES parts(id) ON DELETE CASCADE;


--
-- Name: subscribed_parts_subscriber_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY subscriptions
    ADD CONSTRAINT subscribed_parts_subscriber_id_fkey FOREIGN KEY (subscriber_id) REFERENCES subscribers(id) ON DELETE CASCADE;


--
-- Name: verification_codes_subscriber_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY verification_codes
    ADD CONSTRAINT verification_codes_subscriber_id_fkey FOREIGN KEY (subscriber_id) REFERENCES subscribers(id) ON DELETE CASCADE;


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

