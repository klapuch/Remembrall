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

--
-- Name: record_page_access(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION record_page_access() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
begin
    INSERT INTO page_visits (page_url, visited_at) VALUES (NEW.url, NOW());
    return new;
end
$$;


ALTER FUNCTION public.record_page_access() OWNER TO postgres;

--
-- Name: record_part_access(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION record_part_access() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
begin
    INSERT INTO part_visits (part_id, visited_at) VALUES (NEW.id, NOW());
    return new;
end
$$;


ALTER FUNCTION public.record_part_access() OWNER TO postgres;

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
    "interval" character varying(10) NOT NULL,
    last_update timestamp without time zone NOT NULL
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
-- Name: pages_ai; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER pages_ai AFTER INSERT ON pages FOR EACH ROW EXECUTE PROCEDURE record_page_access();


--
-- Name: pages_au; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER pages_au AFTER UPDATE ON pages FOR EACH ROW EXECUTE PROCEDURE record_page_access();


--
-- Name: parts_ai; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER parts_ai AFTER INSERT ON parts FOR EACH ROW EXECUTE PROCEDURE record_part_access();


--
-- Name: parts_au; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER parts_au AFTER UPDATE ON parts FOR EACH ROW EXECUTE PROCEDURE record_part_access();


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

