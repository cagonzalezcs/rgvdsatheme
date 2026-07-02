import { describe, expect, it } from "vitest";
import categoriesJson from "../../../categories.json";
import blogPostFixture from "../../../tests/fixtures/blog-post.json";
import categoriesFixture from "../../../tests/fixtures/categories.json";
import chapterEventFixture from "../../../tests/fixtures/chapter-event.json";
import postsEnvelopeFixture from "../../../tests/fixtures/posts-envelope.json";
import singlePostFixture from "../../../tests/fixtures/single-post.json";
import {
  blogPostSchema,
  categoriesEnvelopeSchema,
  chapterEventSchema,
  POST_CATS,
  postsEnvelopeSchema,
  singlePostEnvelopeSchema,
} from "@/lib/schemas";

/* The TS half of the dual-sided contract tests: the same committed fixtures
 * PHPUnit asserts byte-equality against (tests/test-contracts.php) must
 * parse with the zod schemas. A serializer or schema change fails one side
 * until both layers agree. */

describe("contract fixtures parse with the zod schemas", () => {
  it("blog-post.json → blogPostSchema", () => {
    expect(() => blogPostSchema.parse(blogPostFixture)).not.toThrow();
  });

  it("single-post.json → singlePostEnvelopeSchema", () => {
    expect(() => singlePostEnvelopeSchema.parse(singlePostFixture)).not.toThrow();
  });

  it("posts-envelope.json → postsEnvelopeSchema", () => {
    expect(() => postsEnvelopeSchema.parse(postsEnvelopeFixture)).not.toThrow();
  });

  it("chapter-event.json → chapterEventSchema", () => {
    expect(() => chapterEventSchema.parse(chapterEventFixture)).not.toThrow();
  });

  it("categories.json envelope → categoriesEnvelopeSchema", () => {
    const parsed = categoriesEnvelopeSchema.parse(categoriesFixture);
    expect(parsed.categories).toHaveLength(6);
  });
});

describe("canonical category slugs", () => {
  it("POST_CATS matches the categories.json registry", () => {
    expect([...POST_CATS]).toEqual(categoriesJson.map((c) => c.id));
  });
});
