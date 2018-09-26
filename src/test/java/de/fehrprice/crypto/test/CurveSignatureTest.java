package de.fehrprice.crypto.test;

import static org.junit.jupiter.api.Assertions.assertEquals;

import org.junit.jupiter.api.AfterAll;
import org.junit.jupiter.api.BeforeAll;
import org.junit.jupiter.api.Test;

import de.fehrprice.crypto.Curve25519;

public class CurveSignatureTest {
	
    public static boolean disableLongRunningTest = true;
	
	private static Curve25519 crv;
	
	@BeforeAll
	public static void setUp() throws Exception {
		crv = new Curve25519();
	}

	@AfterAll
	public static void tearDown() throws Exception {
	}

	/*
	 * String API tests
	 */

/*
ALGORITHM:
Ed25519
SECRET KEY:
9d61b19deffd5a60ba844af492ec2cc4
4449c5697b326919703bac031cae7f60
PUBLIC KEY:
d75a980182b10ab7d54bfed3c964073a
0ee172f3daa62325af021a68f707511a
MESSAGE (length 0 bytes):
SIGNATURE:
e5564300c360ac729086e2cc806e828a
84877f1eb8e5d974d873e06522490155
5fb8821590a33bacc61e39701cf9b46b
d25bf5f0595bbe24655141438e7a100b
 */
	/**
	 * Test ed25519 according to RFC 8032, section 7.1. test vectors
	 * Test single ed25519 examples
	 */
	@Test
	public void Test1() {
		String secretKeyString, publicKeyString, messageString, signatureString;
		secretKeyString = "9d61b19deffd5a60ba844af492ec2cc44449c5697b326919703bac031cae7f60";
		publicKeyString = "d75a980182b10ab7d54bfed3c964073a0ee172f3daa62325af021a68f707511a";
		messageString   = "";
		signatureString = "e5564300c360ac729086e2cc806e828a84877f1eb8e5d974d873e065224901555fb8821590a33bacc61e39701cf9b46bd25bf5f0595bbe24655141438e7a100b";
		String scalarString, uInString, uOutString, uOut;
		
		// first set of vectors
		scalarString = "a546e36bf0527c9d3b16154b82465edd62144c0ac1fc5a18506a2244ba449ac4";
		uInString    = "e6db6867583030db3594c1a424b15f7c726624ec26b3353b10a903a6d0ab1c4c";
		uOutString   = "c3da55379de9c6908e94ea4df28d084f32eccf03491c71f754b4075577a28552";
		
		uOut = crv.x25519(scalarString,uInString);
		assertEquals(uOutString, uOut);
		
		// second set of vectors
		scalarString = "4b66e9d4d1b4673c5ad22691957d6af5c11b6421e0ea01d42ca4169e7918ba0d";
		uInString    = "e5210f12786811d3f4b7959d0538ae2c31dbe7106fc03c3efc4cd549c715a493";
		uOutString   = "95cbde9476e8907d7aade45cb4b873f88b595a68799fa152e6f8f7647aac7957";
		
		uOut = crv.x25519(scalarString,uInString);
		assertEquals(uOutString, uOut);
	}

}

